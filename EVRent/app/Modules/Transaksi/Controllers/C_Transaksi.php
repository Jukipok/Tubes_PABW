<?php

namespace App\Modules\Transaksi\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Kendaraan\Models\M_KendaraanListrik;
use App\Modules\Transaksi\Models\M_Pemesanan;
use App\Modules\Pembayaran\Models\M_Pembayaran;
use App\Modules\Auth\Models\M_Pelanggan;
use App\Modules\Pembayaran\Models\M_Denda;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class C_Transaksi extends Controller
{
    public function create($id_kendaraan)
    {
        $kendaraan = M_KendaraanListrik::findOrFail($id_kendaraan);
        
        return view('transaksi.booking', compact('kendaraan'));
    }

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

        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first();

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

        $kendaraan->update(['status_ketersediaan' => 'disewa']);

        return redirect()->route('payment.create', ['id' => $pemesanan->id_pemesanan]);
    }

    public function createPayment($id_pemesanan)
    {
        $pemesanan = M_Pemesanan::findOrFail($id_pemesanan);
        return view('transaksi.payment', compact('pemesanan'));
    }

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

        $pemesanan->update(['status_sewa' => 'dibayar']);

        return redirect()->route('katalog')->with('success', 'Pembayaran berhasil dikirim. Menunggu verifikasi.');
    }

    public function history()
    {
        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first();

        if (!$pelanggan) {
            return redirect()->route('home')->with('error', 'Akun Anda bukan akun pelanggan.');
        }

        $pemesanans = M_Pemesanan::with(['kendaraan', 'denda'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transaksi.history', compact('pemesanans'));
    }

    public function adminPayments()
    {
        $payments = M_Pembayaran::with(['pemesanan.pelanggan.user', 'pemesanan.kendaraan'])
            ->whereIn('status_bayar', ['pending', 'menunggu_verifikasi'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pembayaran.index', compact('payments'));
    }

    public function verifyPayment(Request $request, $id_pembayaran)
    {
        $payment = M_Pembayaran::findOrFail($id_pembayaran);

        $payment->update([
            'status_bayar' => 'terverifikasi'
        ]);

        if ($payment->pemesanan) {
            $payment->pemesanan->update(['status_sewa' => 'dibayar']);
        }

        return redirect()->route('admin.pembayaran.index')->with('success', 'Pembayaran telah diverifikasi.');
    }

    public function returnItem($id_pemesanan)
    {
        $pemesanan = M_Pemesanan::findOrFail($id_pemesanan);
        
        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first();
        if ($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan) {
            return back()->with('error', 'Unauthorized.');
        }

        if (!in_array($pemesanan->status_sewa, ['dibayar', 'berlangsung'])) {
             return back()->with('error', 'Status pesanan tidak valid untuk pengembalian.');
        }

        $now = Carbon::now();
        $due = Carbon::parse($pemesanan->tanggal_kembali);
        $fineMessage = '';

        if ($now->greaterThan($due)) {
            $hoursLate = $now->diffInHours($due);
            $hoursLate = $now->floatDiffInHours($due);
            $hoursLateCeil = ceil($hoursLate);
            
            $kendaraan = M_KendaraanListrik::find($pemesanan->id_kendaraan);
            $pricePerHour = $kendaraan->harga_perjam;
            $fineAmount = $hoursLateCeil * $pricePerHour * 2;

            \App\Modules\Pembayaran\Models\M_Denda::create([
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'jenis_denda' => 'Keterlambatan ' . $hoursLateCeil . ' Jam',
                'total_denda' => $fineAmount,
                'status_denda' => 'belum_dibayar'
            ]);

            $fineMessage = ' Anda terlambat ' . $hoursLateCeil . ' jam. Denda: Rp ' . number_format($fineAmount);
        }

        $pemesanan->update(['status_sewa' => 'selesai']);

        $kendaraan = M_KendaraanListrik::find($pemesanan->id_kendaraan);
        if ($kendaraan) {
            $kendaraan->update(['status_ketersediaan' => 'tersedia']);
        }

        return back()->with('success', 'Kendaraan berhasil dikembalikan.' . $fineMessage . ' Silakan beri ulasan Anda!');
    }

    public function storeReview(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required|exists:pemesanans,id_pemesanan',
            'id_pemilik_rental' => 'required|exists:pemilik_rentals,id_pemilik_rental',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string|max:500',
        ]);

        $pemesanan = M_Pemesanan::findOrFail($request->id_pemesanan);
        
        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first();
        if ($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan) {
            return back()->with('error', 'Unauthorized.');
        }

        $existingReview = \App\Modules\Laporan\Models\M_Ulasan::where('id_pemilik_rental', $request->id_pemilik_rental)
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->first();

        if ($existingReview) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk rental ini.');
        }

        \App\Modules\Laporan\Models\M_Ulasan::create([
            'id_pemilik_rental' => $request->id_pemilik_rental,
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
        ]);

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }

    public function deleteBooking($id_pemesanan)
    {
        $pemesanan = M_Pemesanan::findOrFail($id_pemesanan);
        
        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first();
        if ($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan) {
            return back()->with('error', 'Unauthorized.');
        }

        if (!in_array($pemesanan->status_sewa, ['menunggu_pembayaran', 'selesai'])) {
            return back()->with('error', 'Pesanan ini tidak dapat dihapus.');
        }

        if ($pemesanan->kendaraan && $pemesanan->status_sewa == 'menunggu_pembayaran') {
            $pemesanan->kendaraan->update(['status_ketersediaan' => 'tersedia']);
        }

        $pemesanan->delete();

        return redirect()->route('my_bookings')->with('success', 'Pesanan berhasil dihapus.');
    }

    public function calculateFine($id_pemesanan)
    {
        return "Not implemented yet";
    }

    public function adminReviews()
    {
        $reviews = \App\Modules\Laporan\Models\M_Ulasan::with(['pelanggan.user', 'pemilikRental'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.ulasan.index', compact('reviews'));
    }

    public function xenditReport()
    {
        $payments = \App\Modules\Pembayaran\Models\M_XenditPayment::orderBy('created_at', 'desc')->get();
        return view('admin.laporan.xendit', compact('payments'));
    }

    public function ownerDashboard()
    {
        $user = Auth::user();

        $pemilik = \App\Modules\Auth\Models\M_PemilikRental::where('id_user', $user->id)->first();
        
        if (!$pemilik) {
            return redirect()->route('home')->with('error', 'Profile Pemilik tidak ditemukan.');
        }

        $vehicleIds = M_KendaraanListrik::where('id_pemilik_rental', $pemilik->id_pemilik_rental)
            ->pluck('id_kendaraan');

        $totalPendapatan = M_Pemesanan::whereIn('id_kendaraan', $vehicleIds)
            ->whereIn('status_sewa', ['dibayar', 'selesai'])
            ->sum('total_biaya');

        $transaksiAktif = M_Pemesanan::whereIn('id_kendaraan', $vehicleIds)
            ->whereIn('status_sewa', ['menunggu_pembayaran', 'dibayar', 'disewa', 'berlangsung'])
            ->count();

        $monthlyStats = M_Pemesanan::whereIn('id_kendaraan', $vehicleIds)
            ->whereIn('status_sewa', ['dibayar', 'selesai'])
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(tanggal_sewa, "%Y-%m") as month, SUM(total_biaya) as total')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $labels = [];
        $revenueData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $labels[] = $date->format('M Y');
            
            $stat = $monthlyStats->firstWhere('month', $monthKey);
            $revenueData[] = $stat ? $stat->total : 0;
        }

        return view('dashboard.owner', compact('totalPendapatan', 'transaksiAktif', 'labels', 'revenueData'));
    }

    public function ownerFinancialReport()
    {
        $user = Auth::user();
        $pemilik = \App\Modules\Auth\Models\M_PemilikRental::where('id_user', $user->id)->first();

        if (!$pemilik) {
            return redirect()->route('home')->with('error', 'Profile Pemilik tidak ditemukan.');
        }

        $vehicleIds = M_KendaraanListrik::where('id_pemilik_rental', $pemilik->id_pemilik_rental)
            ->pluck('id_kendaraan');

        $transactions = M_Pemesanan::with(['pelanggan.user', 'kendaraan'])
            ->whereIn('id_kendaraan', $vehicleIds)
            ->whereIn('status_sewa', ['dibayar', 'selesai', 'menunggu_pembayaran'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.owner_financial', compact('transactions'));
    }

    public function ownerReviews()
    {
        $user = Auth::user();
        $pemilik = \App\Modules\Auth\Models\M_PemilikRental::where('id_user', $user->id)->first();

        if (!$pemilik) {
            return redirect()->route('home')->with('error', 'Profile Pemilik tidak ditemukan.');
        }

        $reviews = \App\Modules\Laporan\Models\M_Ulasan::with(['pelanggan.user'])
            ->where('id_pemilik_rental', $pemilik->id_pemilik_rental)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.owner_reviews', compact('reviews'));
    }
}
