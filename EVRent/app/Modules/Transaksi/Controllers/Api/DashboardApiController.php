<?php

namespace App\Modules\Transaksi\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Transaksi\Models\M_Pemesanan;
use App\Modules\Kendaraan\Models\M_KendaraanListrik;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    /**
     * Get Owner Dashboard Stats
     * GET /api/dashboard/owner-stats
     */
    public function ownerStats(Request $request)
    {
        $user = $request->user();

        // Ensure user is 'pemilik_rental' or 'admin_evrent'
        if (!in_array($user->role, ['pemilik_rental', 'admin_evrent'])) {
            return response()->json(['message' => 'Unauthorized. Only owners can view stats.'], 403);
        }

        // Get owner's rental ID from relationship if needed, or query vehicles by owner
        // Assuming M_KendaraanListrik has a 'id_pemilik_rental' or we use user relationship
        // Checking M_KendaraanListrik from previous context: it has 'pemilik()' relation to M_PemilikRental
        
        $pemilik = $user->pemilikRental;
        if (!$pemilik && $user->role !== 'admin_evrent') {
             return response()->json(['message' => 'Owner profile not found.'], 404);
        }
        
        // Define scope: if Admin, everything. If Owner, only their vehicles.
        $vehicleIds = M_KendaraanListrik::query();
        
        if ($user->role === 'pemilik_rental') {
            $vehicleIds->where('id_pemilik_rental', $pemilik->id_pemilik);
        }
        $vehicleIds = $vehicleIds->pluck('id_kendaraan');

        // 1. Total Pendapatan (Status 'dibayar' atau 'selesai')
        $totalPendapatan = M_Pemesanan::whereIn('id_kendaraan', $vehicleIds)
            ->whereIn('status_sewa', ['dibayar', 'selesai'])
            ->sum('total_biaya');

        // 2. Total Transaksi (Semua status kecuali dibatalkan/pending lama mungkin? Atau semua booking)
        $totalTransaksi = M_Pemesanan::whereIn('id_kendaraan', $vehicleIds)
            ->count();

        // 3. Transaksi Aktif (Sedang disewa/menunggu pembayaran/dibayar tapi belum selesai)
        $transaksiAktif = M_Pemesanan::whereIn('id_kendaraan', $vehicleIds)
            ->whereIn('status_sewa', ['menunggu_pembayaran', 'dibayar', 'disewa'])
            ->count();

        // 4. Jumlah Kendaraan
        $jumlahKendaraan = $user->role === 'pemilik_rental' 
            ? M_KendaraanListrik::where('id_pemilik_rental', $pemilik->id_pemilik)->count()
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
