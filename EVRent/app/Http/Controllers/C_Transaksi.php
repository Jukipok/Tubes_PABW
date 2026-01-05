<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\M_KendaraanListrik;
use App\Models\M_Pemesanan;
use App\Models\M_Pembayaran;
use App\Models\M_Pelanggan;
use App\Models\M_Denda;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class C_Transaksi extends Controller
{
    // Show Booking Form
    public function create($id_kendaraan)
    {
        $kendaraan = M_KendaraanListrik::findOrFail($id_kendaraan);
        
        // Fetch reviews
        $ulasans = \App\Models\M_Ulasan::whereHas('pemesanan', function($q) use ($id_kendaraan) {
            $q->where('id_kendaraan', $id_kendaraan);
        })->with('pemesanan.pelanggan.user')->orderBy('created_at', 'desc')->get();

        return view('transaksi.booking', compact('kendaraan', 'ulasans'));
    }

    // Process Booking (Simpan Pemesanan)
    public function store(Request $request)
    {
        $request->validate([
            'id_kendaraan' => 'required|exists:kendaraan_listriks,id_kendaraan',
            'tanggal_sewa' => 'required|date|after_or_equal:today',
            'durasi_sewa' => 'required|integer|min:1',
        ]);

        $kendaraan = M_KendaraanListrik::findOrFail($request->id_kendaraan);
        $total_biaya = $kendaraan->harga_perjam * $request->durasi_sewa;
        
        $tanggal_sewa = Carbon::parse($request->tanggal_sewa);
        $tanggal_kembali = $tanggal_sewa->copy()->addHours((int) $request->durasi_sewa);

        // Get Pelanggan ID from Auth User
        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first(); // Auth::id() is PK of users table. id_user in pelanggans is FK.

        if (!$pelanggan) {
            return redirect()->back()->with('error', 'Profile Pelanggan tidak ditemukan.');
        }

        $pemesanan = M_Pemesanan::create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'id_kendaraan' => $request->id_kendaraan,
            'tanggal_sewa' => $tanggal_sewa,
            'tanggal_kembali' => $tanggal_kembali,
            'durasi_sewa' => $request->durasi_sewa,
            'total_biaya' => $total_biaya,
            'status_sewa' => 'menunggu_pembayaran',
        ]);

        // Update Vehicle Status
        $kendaraan->update(['status_ketersediaan' => 'disewa']);

        // Redirect directly to Xendit Payment
        return redirect()->route('payment.create', ['id' => $pemesanan->id_pemesanan]);
    }

    // Show Payment Form
    public function createPayment($id_pemesanan)
    {
        $pemesanan = M_Pemesanan::findOrFail($id_pemesanan);
        return view('transaksi.payment', compact('pemesanan'));
    }

    // Process Payment
    public function processPayment(Request $request, $id_pemesanan)
    {
        $pemesanan = M_Pemesanan::findOrFail($id_pemesanan);

        $request->validate([
            'metode_pembayaran' => 'required|string',
            'bukti_transfer' => 'required|file|image|max:2048',
        ]);

        $path = $request->file('bukti_transfer')->store('pembayaran', 'public');

        M_Pembayaran::create([
            'id_pemesanan' => $id_pemesanan,
            'metode_pembayaran' => $request->metode_pembayaran,
            'jumlah_bayar' => $pemesanan->total_biaya,
            'tanggal_bayar' => now(),
            'bukti_transfer' => $path,
            'status_bayar' => 'menunggu_verifikasi',
        ]);

        $pemesanan->update(['status_sewa' => 'dibayar']); // Or 'menunggu_verifikasi' if strict

        return redirect()->route('katalog')->with('success', 'Pembayaran berhasil dikirim. Menunggu verifikasi.');
    }

    // Booking History
    public function history()
    {
        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first();

        if (!$pelanggan) {
            return redirect()->route('home')->with('error', 'Akun Anda bukan akun pelanggan.');
        }

        $pemesanans = M_Pemesanan::join('kendaraan_listriks', 'pemesanans.id_kendaraan', '=', 'kendaraan_listriks.id_kendaraan')
            ->where('pemesanans.id_pelanggan', $pelanggan->id_pelanggan)
            ->where('pemesanans.id_pelanggan', $pelanggan->id_pelanggan)
            ->with(['ulasan', 'denda']) // Eager load ulasan and denda
            ->select('pemesanans.*', 'kendaraan_listriks.merk_kendaraan', 'kendaraan_listriks.tipe_kendaraan', 'kendaraan_listriks.gambar_kendaraan', 'kendaraan_listriks.plat_nomor')
            ->orderBy('pemesanans.created_at', 'desc')
            ->get();

        return view('transaksi.history', compact('pemesanans'));
    }

    // Return Vehicle (Finish Rental)
    public function returnItem($id_pemesanan)
    {
        $pemesanan = M_Pemesanan::findOrFail($id_pemesanan);
        
        // Ensure user owns this booking
        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first();
        if ($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan) {
            return back()->with('error', 'Unauthorized.');
        }

        // Only allowed if status is 'dibayar' (active) or 'berlangsung'
        if (!in_array($pemesanan->status_sewa, ['dibayar', 'berlangsung'])) {
             return back()->with('error', 'Status pesanan tidak valid untuk pengembalian.');
        }

        // Calculate Fine (Denda)
        $now = Carbon::now();
        $due = Carbon::parse($pemesanan->tanggal_kembali);
        $fineMessage = '';

        if ($now->greaterThan($due)) {
            $hoursLate = $now->diffInHours($due);
            // If late less than an hour but passed time, count as 1 hour or use float? 
            // Let's use ceil for hours
            $hoursLate = $now->floatDiffInHours($due);
            $hoursLateCeil = ceil($hoursLate); // Minimum 1 hour if late
            
            // Logic: Fine = 2 * Price * Hours Late
            $kendaraan = M_KendaraanListrik::find($pemesanan->id_kendaraan);
            $pricePerHour = $kendaraan->harga_perjam;
            $fineAmount = $hoursLateCeil * $pricePerHour * 2;

            \App\Models\M_Denda::create([
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'jenis_denda' => 'Keterlambatan ' . $hoursLateCeil . ' Jam',
                'total_denda' => $fineAmount,
                'status_denda' => 'belum_dibayar'
            ]);

            $fineMessage = ' Anda terlambat ' . $hoursLateCeil . ' jam. Denda: Rp ' . number_format($fineAmount);
        }

        // Update Booking Status
        $pemesanan->update(['status_sewa' => 'selesai']);

        // Update Vehicle Status back to Available
        $kendaraan = M_KendaraanListrik::find($pemesanan->id_kendaraan);
        if ($kendaraan) {
            $kendaraan->update(['status_ketersediaan' => 'tersedia']);
        }

        return back()->with('success', 'Kendaraan berhasil dikembalikan.' . $fineMessage . ' Silakan beri ulasan Anda!');
    }

    // Submit Review
    public function storeReview(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required|exists:pemesanans,id_pemesanan',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string|max:500',
        ]);

        $pemesanan = M_Pemesanan::findOrFail($request->id_pemesanan);
        
        // Ensure user owns this booking
        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first();
        if ($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan) {
            return back()->with('error', 'Unauthorized.');
        }

        \App\Models\M_Ulasan::create([
            'id_pemesanan' => $request->id_pemesanan,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
        ]);

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }

    // Calculate Fine (Simulation)
    public function hitungDenda($id_pemesanan)
    {
        // Logic to calculate fine if returned late
        // For now, just a view or json
        return "Not implemented yet";
    }
}
