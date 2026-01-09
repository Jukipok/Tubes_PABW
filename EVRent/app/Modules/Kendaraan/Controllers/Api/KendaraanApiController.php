<?php

namespace App\Modules\Kendaraan\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Modules\Kendaraan\Models\M_KendaraanListrik;

class KendaraanApiController extends Controller
{
    public function index(Request $request)
    {
        $query = M_KendaraanListrik::with('pemilik')->where('status_ketersediaan', 'tersedia');

        if ($request->has('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->has('search')) {
            $query->where('merk_kendaraan', 'like', '%' . $request->search . '%')
                  ->orWhere('tipe_kendaraan', 'like', '%' . $request->search . '%');
        }

        $kendaraans = $query->get();

        return response()->json([
            'data' => $kendaraans
        ]);
    }

    public function show($id)
    {
        $kendaraan = M_KendaraanListrik::with('pemilik')->find($id);

        if (!$kendaraan) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        return response()->json([
            'data' => $kendaraan
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin_evrent', 'pemilik_rental'])) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'merk_kendaraan' => 'required',
            'tipe_kendaraan' => 'required',
            'jenis' => 'required|in:mobil,motor,sepeda',
            'plat_nomor' => 'required|unique:kendaraan_listriks',
            'harga_perjam' => 'required|numeric',
            'gambar' => 'nullable|image|max:2048', // Max 2MB
        ]);

        $id_pemilik = null;
        if ($user->role === 'pemilik_rental') {
             $owner = \App\Modules\Auth\Models\M_PemilikRental::where('id_user', $user->id)->first();
             $id_pemilik = $owner->id_pemilik_rental ?? null;
        } else {
             $id_pemilik = $request->id_pemilik_rental;
        }
        
        if (!$id_pemilik) { return response()->json(['message' => 'Owner ID required'], 400); }

        $path = null;
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('kendaraan', 'public');
        }

        $kendaraan = M_KendaraanListrik::create([
            'merk_kendaraan' => $request->merk_kendaraan,
            'tipe_kendaraan' => $request->tipe_kendaraan,
            'jenis' => $request->jenis,
            'plat_nomor' => $request->plat_nomor,
            'harga_perjam' => $request->harga_perjam,
            'status_ketersediaan' => 'tersedia',
            'gambar' => $path,
            'id_pemilik_rental' => $id_pemilik
        ]);

        return response()->json(['message' => 'Vehicle created', 'data' => $kendaraan], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin_evrent', 'pemilik_rental'])) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kendaraan = M_KendaraanListrik::find($id);
        if (!$kendaraan) { return response()->json(['message' => 'Not found'], 404); }
        
        if ($user->role === 'pemilik_rental') {
             $owner = \App\Modules\Auth\Models\M_PemilikRental::where('id_user', $user->id)->first();
             if ($kendaraan->id_pemilik_rental !== $owner->id_pemilik_rental) {
                 return response()->json(['message' => 'Unauthorized'], 403);
             }
        }

        $kendaraan->fill($request->only([
            'merk_kendaraan', 'tipe_kendaraan', 'jenis', 'harga_perjam', 'status_ketersediaan'
        ]));

        if ($request->hasFile('gambar')) {
            if ($kendaraan->gambar && Storage::disk('public')->exists($kendaraan->gambar)) {
                Storage::disk('public')->delete($kendaraan->gambar);
            }
            $path = $request->file('gambar')->store('kendaraan', 'public');
            $kendaraan->gambar = $path;
        }

        $kendaraan->save();

        return response()->json(['message' => 'Vehicle updated', 'data' => $kendaraan]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin_evrent', 'pemilik_rental'])) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kendaraan = M_KendaraanListrik::find($id);
        if (!$kendaraan) { return response()->json(['message' => 'Not found'], 404); }

        if ($user->role === 'pemilik_rental') {
             $owner = \App\Modules\Auth\Models\M_PemilikRental::where('id_user', $user->id)->first();
             if ($kendaraan->id_pemilik_rental !== $owner->id_pemilik_rental) {
                 return response()->json(['message' => 'Unauthorized'], 403);
             }
        }

        $kendaraan->delete();
        return response()->json(['message' => 'Vehicle deleted']);
    }
}
