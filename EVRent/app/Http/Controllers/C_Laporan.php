<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\M_Pemesanan;
use App\Models\M_Laporan;
use App\Models\M_Pelanggan;
use Illuminate\Support\Facades\Auth;

class C_Laporan extends Controller
{
    // Show Report Form
    public function create($id_pemesanan)
    {
        $pemesanan = M_Pemesanan::findOrFail($id_pemesanan);
        
        // Ensure user owns this booking
        $pelanggan = M_Pelanggan::where('id_user', Auth::id())->first();
        if ($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan) {
            return back()->with('error', 'Unauthorized.');
        }

        return view('laporan.create', compact('pemesanan'));
    }

    // Store Report
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
}
