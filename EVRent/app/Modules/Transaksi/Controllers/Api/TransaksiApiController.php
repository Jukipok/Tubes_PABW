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

        $pemesanans = M_Pemesanan::with(['kendaraan', 'denda'])
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

    // Cancel Booking (Delete)
    public function destroy($id)
    {
        $user = Auth::user();
        $testPelanggan = M_Pelanggan::where('id_user', $user->id)->first();

        $pemesanan = M_Pemesanan::find($id);
        if (!$pemesanan) { return response()->json(['message' => 'Not found'], 404); }

        if ($pemesanan->id_pelanggan !== $testPelanggan->id_pelanggan) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($pemesanan->status_sewa !== 'menunggu_pembayaran') {
             return response()->json(['message' => 'Cannot cancel active or finished booking'], 400);
        }

        // Return vehicle to available
        if ($pemesanan->kendaraan) {
             $pemesanan->kendaraan->update(['status_ketersediaan' => 'tersedia']);
        }

        $pemesanan->delete();
        return response()->json(['message' => 'Booking cancelled']);
    }
}
