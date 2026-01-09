<?php

namespace App\Modules\Laporan\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Transaksi\Models\M_Pemesanan;
use App\Modules\Laporan\Models\M_Laporan;
use App\Modules\Auth\Models\M_Pelanggan;
use Illuminate\Support\Facades\Auth;

class C_Laporan extends Controller
{
    public function create($id_pemesanan)
    {
        $pemesanan = M_Pemesanan::findOrFail($id_pemesanan);
        
        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first();
        if ($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan) {
            return back()->with('error', 'Unauthorized.');
        }

        return view('laporan.create', compact('pemesanan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required|exists:pemesanans,id_pemesanan',
            'jenis_laporan' => 'required|in:kerusakan_awal,kerusakan_akhir,lainnya',
            'deskripsi_masalah' => 'required|string|max:1000',
            'foto_bukti' => 'nullable|file|image|max:2048'
        ]);

        $path = null;
        if ($request->hasFile('foto_bukti')) {
            $path = $request->file('foto_bukti')->store('laporan', 'public');
        }

        M_Laporan::create([
            'id_pemesanan' => $request->id_pemesanan,
            'jenis_laporan' => $request->jenis_laporan,
            'deskripsi_masalah' => $request->deskripsi_masalah,
            'foto_bukti' => $path,
            'status_laporan' => 'pending'
        ]);

        return redirect()->route('my_bookings')->with('success', 'Laporan Anda berhasil dikirim dan akan segera diproses.');
    }

    public function adminReports()
    {
        $reports = M_Laporan::with(['pemesanan.pelanggan.user', 'pemesanan.kendaraan.pemilik'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.laporan.masalah', compact('reports'));
    }

    public function ownerReports()
    {
        $user = Auth::user();
        $pemilik = \App\Modules\Auth\Models\M_PemilikRental::where('id_user', $user->id)->first();

        if (!$pemilik) {
             return redirect()->route('home')->with('error', 'Profile Pemilik tidak ditemukan.');
        }

        $reports = M_Laporan::with(['pemesanan.pelanggan.user', 'pemesanan.kendaraan'])
            ->whereHas('pemesanan.kendaraan', function($q) use ($pemilik) {
                $q->where('id_pemilik_rental', $pemilik->id_pemilik_rental);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.owner_complaints', compact('reports'));
    }
}
