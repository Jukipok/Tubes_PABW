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

        $kendaraan->update(['status_ketersediaan' => 'disewa']);

        return response()->json([
            'message' => 'Booking created successfully',
            'data' => $pemesanan
        ], 201);
    }

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

    public function returnVehicle($id)
    {
        $pemesanan = M_Pemesanan::find($id);

        if (!$pemesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }
        
        $pemesanan->update([
            'status_sewa' => 'selesai',
            'tanggal_kembali' => Carbon::now()
        ]);

        $kendaraan = M_KendaraanListrik::find($pemesanan->id_kendaraan);
        if ($kendaraan) {
            $kendaraan->update(['status_ketersediaan' => 'tersedia']);
        }

        return response()->json([
            'message' => 'Kendaraan berhasil dikembalikan',
            'data' => $pemesanan
        ]);
    }

    public function destroy($id)
    {
        $pemesanan = M_Pemesanan::find($id);
        
        if ($pemesanan) {
            if ($pemesanan->status_sewa == 'berlangsung' && $pemesanan->kendaraan) {
                 $pemesanan->kendaraan->update(['status_ketersediaan' => 'tersedia']);
            }
            
            $pemesanan->delete();
            return response()->json(['message' => 'Pesanan dihapus']);
        }

        return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
    }
    public function ownerHistory(Request $request)
    {
        $user = $request->user();
        
        $pemilik = \App\Modules\Auth\Models\M_PemilikRental::where('id_user', $user->id)->first();

        if (!$pemilik && $user->role !== 'admin_evrent') {
             return response()->json(['message' => 'Owner profile not found'], 404);
        }

        $vehicleIds = M_KendaraanListrik::query();
        if ($user->role === 'pemilik_rental') {
             $vehicleIds->where('id_pemilik_rental', $pemilik->id_pemilik_rental);
        }
        $vehicleIds = $vehicleIds->pluck('id_kendaraan');

        $bookings = M_Pemesanan::with(['kendaraan', 'pelanggan.user'])
                    ->whereIn('id_kendaraan', $vehicleIds)
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
        return response()->json(['data' => $bookings]);
    }
}
