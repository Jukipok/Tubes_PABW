<?php

namespace App\Modules\Transaksi\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Transaksi\Models\M_Pemesanan;
use App\Modules\Kendaraan\Models\M_KendaraanListrik;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    public function ownerStats(Request $request)
    {
        $user = $request->user();


        
        $pemilik = $user->pemilikRental;
        if (!$pemilik && $user->role !== 'admin_evrent') {
             return response()->json(['message' => 'Owner profile not found.'], 404);
        }
        
        $vehicleIds = M_KendaraanListrik::query();
        
        if ($user->role === 'pemilik_rental') {
            $vehicleIds->where('id_pemilik_rental', $pemilik->id_pemilik_rental);
        }
        $vehicleIds = $vehicleIds->pluck('id_kendaraan');

        $jumlahKendaraan = $user->role === 'pemilik_rental' 
            ? M_KendaraanListrik::where('id_pemilik_rental', $pemilik->id_pemilik_rental)->count()
            : M_KendaraanListrik::count();

        return response()->json([
            'total_pendapatan' => (float) $totalPendapatan,
            'total_transaksi' => $totalTransaksi,
            'transaksi_aktif' => $transaksiAktif,
            'jumlah_kendaraan' => $jumlahKendaraan,
            'owner_info' => [
                'name' => $user->nama_lengkap ?? $user->name,
                'role' => $user->role
            ]
        ]);
    }
}
