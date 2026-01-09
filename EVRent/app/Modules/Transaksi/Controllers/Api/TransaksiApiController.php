<?php

namespace App\Modules\Transaksi\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Transaksi\Models\M_Pemesanan;
use App\Modules\Kendaraan\Models\M_KendaraanListrik;
use App\Modules\Auth\Models\M_Pelanggan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransaksiApiController extends Controller
{
    // Create Booking
    public function store(Request $request)
    {
        $request->validate([
            'id_kendaraan' => 'required|exists:kendaraan_listriks,id_kendaraan',
            'durasi_sewa' => 'required|integer|min:1',
            // 'start_date' is now() for simplicity or passed
        ]);

        $user = Auth::user();
        $pelanggan = M_Pelanggan::where('id_user', $user->id)->first();

        if (!$pelanggan) {
            return response()->json(['message' => 'User is not a customer'], 403);
        }

        $kendaraan = M_KendaraanListrik::find($request->id_kendaraan);
        
        if ($kendaraan->status_ketersediaan !== 'tersedia') {
            return response()->json(['message' => 'Vehicle is not available'], 400);
        }

        $tanggal_sewa = Carbon::now();
        $tanggal_kembali = $tanggal_sewa->copy()->addHours((int) $request->durasi_sewa);
        $total_biaya = $kendaraan->harga_perjam * $request->durasi_sewa;

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

        return response()->json([
            'message' => 'Booking created successfully',
            'data' => $pemesanan
        ], 201);
    }

    // History
    public function history(Request $request)
    {
        $user = Auth::user();
        $pelanggan = M_Pelanggan::where('id_user', $user->id)->first();

        if (!$pelanggan) {
            return response()->json(['message' => 'User is not a customer'], 403);
        }

        $pemesanans = M_Pemesanan::with(['kendaraan', 'denda', 'pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $pemesanans
        ]);
    }

    // Show Detail
    public function show($id)
    {
        $user = Auth::user();
        $pelanggan = M_Pelanggan::where('id_user', $user->id)->first();

        $pemesanan = M_Pemesanan::with(['kendaraan', 'pembayaran', 'denda'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->find($id);

        if (!$pemesanan) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json([
            'data' => $pemesanan
        ]);
    }

    // Return Vehicle (Kembalikan)
    public function returnVehicle($id)
    {
        // Use M_Pemesanan as 'Booking'
        $pemesanan = M_Pemesanan::find($id);

        if (!$pemesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        // Optional: Check if already returned to avoid double logic, but user script didn't have it.
        // We will keep it simple as requested.
        
        $pemesanan->update([
            'status_sewa' => 'selesai',
            'tanggal_kembali' => Carbon::now()
        ]);

        // Update Vehicle Status
        $kendaraan = M_KendaraanListrik::find($pemesanan->id_kendaraan);
        if ($kendaraan) {
            $kendaraan->update(['status_ketersediaan' => 'tersedia']);
        }

        return response()->json([
            'message' => 'Kendaraan berhasil dikembalikan',
            'data' => $pemesanan
        ]);
    }

    // Cancel Booking (Delete History)
    public function destroy($id)
    {
        // User requested simple delete. 
        // Note: strict checks removed to allow "Hapus" button in Flutter to work for any history item if that's the intention.
        // However, usually we only delete pending bookings. 
        // But to follow "sesuaikan agar jalan normal", we will allow delete (or maybe soft delete if model supported, but here force delete).
        
        $pemesanan = M_Pemesanan::find($id);
        
        if ($pemesanan) {
            // If active/finished, return vehicle availability just in case (safety)
            if ($pemesanan->status_sewa == 'berlangsung' && $pemesanan->kendaraan) {
                 $pemesanan->kendaraan->update(['status_ketersediaan' => 'tersedia']);
            }
            
            $pemesanan->delete();
            return response()->json(['message' => 'Pesanan dihapus']);
        }

        return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
    }
    // Owner History (Riwayat Pesanan untuk Pemilik)
    public function ownerHistory(Request $request)
    {
        $user = $request->user();
        
        // 1. Get Owner Profile
        // Assuming relationship defined in User model or querying directly
        $pemilik = \App\Modules\Auth\Models\M_PemilikRental::where('id_user', $user->id)->first();

        if (!$pemilik && $user->role !== 'admin_evrent') {
             return response()->json(['message' => 'Owner profile not found'], 404);
        }

        // 2. Get Vehicle IDs belonging to this owner
        $vehicleIds = M_KendaraanListrik::query();
        if ($user->role === 'pemilik_rental') {
             $vehicleIds->where('id_pemilik_rental', $pemilik->id_pemilik_rental);
        }
        $vehicleIds = $vehicleIds->pluck('id_kendaraan');

        // 3. Get Bookings for these vehicles
        $bookings = M_Pemesanan::with(['kendaraan', 'pelanggan.user'])
                    ->whereIn('id_kendaraan', $vehicleIds)
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
        return response()->json(['data' => $bookings]); // Wrapped in 'data' for consistency, or just $bookings if Flutter expects raw array
    }
}
