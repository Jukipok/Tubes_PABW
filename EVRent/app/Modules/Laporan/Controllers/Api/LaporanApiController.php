<?php

namespace App\Modules\Laporan\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Laporan\Models\M_Ulasan;
use App\Modules\Transaksi\Models\M_Pemesanan;
use App\Modules\Auth\Models\M_Pelanggan;
use Illuminate\Support\Facades\Auth;

class LaporanApiController extends Controller
{
    // Submit Review
    public function store(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required|exists:pemesanans,id_pemesanan',
            'id_pemilik_rental' => 'required|exists:pemilik_rentals,id_pemilik_rental',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $pelanggan = M_Pelanggan::where('id_user', $user->id)->first();

        $pemesanan = M_Pemesanan::find($request->id_pemesanan);
        
        if ($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check exists
        $exists = M_Ulasan::where('id_pemesanan', $request->id_pemesanan)->exists();
        if ($exists) {
             return response()->json(['message' => 'Already reviewed'], 400);
        }

        $ulasan = M_Ulasan::create([
            'id_pemilik_rental' => $request->id_pemilik_rental,
            'id_pelanggan' => $pelanggan->id_pelanggan,
            // 'id_pemesanan' => $request->id_pemesanan, // Removed to avoid error if column missing
            'rating' => $request->rating,
            'komentar' => $request->komentar,
        ]);

        return response()->json([
            'message' => 'Review submitted',
            'data' => $ulasan
        ], 201);
    }
    
    // List Reviews
    public function index(Request $request) 
    {
        $query = M_Ulasan::with('pelanggan.user');

        if ($request->has('id_pemilik_rental')) {
            $query->where('id_pemilik_rental', $request->id_pemilik_rental);
        }

        $ulasans = $query->get();

        return response()->json([
            'data' => $ulasans
        ]);
    }

    // --- Complaint / Lapor Masalah APIs ---

    // Store Complaint
    public function storeComplaint(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required|exists:pemesanans,id_pemesanan',
            'jenis_laporan' => 'required|in:kerusakan_awal,kerusakan_akhir,lainnya',
            'deskripsi_masalah' => 'required|string|max:1000',
            'foto_bukti' => 'nullable|file|image|max:2048'
        ]);

        $user = Auth::user();
        $pelanggan = M_Pelanggan::where('id_user', $user->id)->first();
        $pemesanan = M_Pemesanan::find($request->id_pemesanan);

        if (!$pelanggan || $pemesanan->id_pelanggan !== $pelanggan->id_pelanggan) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $path = null;
        if ($request->hasFile('foto_bukti')) {
            $path = $request->file('foto_bukti')->store('laporan', 'public');
        }

        $laporan = \App\Modules\Laporan\Models\M_Laporan::create([
            'id_pemesanan' => $request->id_pemesanan,
            'jenis_laporan' => $request->jenis_laporan,
            'deskripsi_masalah' => $request->deskripsi_masalah,
            'foto_bukti' => $path,
            'status_laporan' => 'pending'
        ]);

        return response()->json([
            'message' => 'Complaint submitted successfully',
            'data' => $laporan
        ], 201);
    }

    // Admin: List All Complaints
    public function adminComplaints()
    {
        // Ensure user is admin (this check usually done by middleware, but good to have safety)
        if (!Auth::user()->hasRole(['admin_evrent', 'admin_sewa'])) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reports = \App\Modules\Laporan\Models\M_Laporan::with(['pemesanan.pelanggan.user', 'pemesanan.kendaraan.pemilik'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['data' => $reports]);
    }

    // Owner: List My Complaints
    public function ownerComplaints()
    {
        $user = Auth::user();
        $pemilik = \App\Modules\Auth\Models\M_PemilikRental::where('id_user', $user->id)->first();

        if (!$pemilik) {
             return response()->json(['message' => 'Owner profile not found'], 404);
        }

        $reports = \App\Modules\Laporan\Models\M_Laporan::with(['pemesanan.pelanggan.user', 'pemesanan.kendaraan'])
            ->whereHas('pemesanan.kendaraan', function($q) use ($pemilik) {
                $q->where('id_pemilik_rental', $pemilik->id_pemilik_rental);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['data' => $reports]);
    }
}
