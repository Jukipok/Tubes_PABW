<?php

namespace App\Modules\Kendaraan\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Kendaraan\Models\M_KendaraanListrik;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class C_Kendaraan extends Controller
{
    // Display a listing of the resource.
    public function index(Request $request)
    {
        // Search functionality
        $selectedRental = null;
        $kendaraans = collect(); // Default empty collection

        if ($request->has('rental_id')) {
            $query = M_KendaraanListrik::query();
            
            if ($request->has('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('merk_kendaraan', 'like', '%' . $request->search . '%')
                      ->orWhere('tipe_kendaraan', 'like', '%' . $request->search . '%');
                });
            }

            $query->where('id_pemilik_rental', $request->rental_id);
            $selectedRental = \App\Modules\Auth\Models\M_PemilikRental::find($request->rental_id);
            $kendaraans = $query->get();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($kendaraans);
        }

        // Fetch Real Locations from DB
        $locations = \App\Modules\Auth\Models\M_PemilikRental::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function($rental) {
                // Calculate Average Rating from Ulasan (now linked to PemilikRental)
                $avgRating = \App\Modules\Laporan\Models\M_Ulasan::where('id_pemilik_rental', $rental->id_pemilik_rental)->avg('rating');
                $reviewCount = \App\Modules\Laporan\Models\M_Ulasan::where('id_pemilik_rental', $rental->id_pemilik_rental)->count();

                return [
                    'id' => $rental->id_pemilik_rental,
                    'name' => $rental->nama_rental ?? 'Rental ' . $rental->id_pemilik_rental,
                    'lat' => $rental->latitude,
                    'lng' => $rental->longitude,
                    'address' => $rental->lokasi_rental,
                    'rating' => $avgRating ? number_format($avgRating, 1) : null,
                    'reviews' => $reviewCount
                ];
            });

        return view('kendaraan.katalog', compact('kendaraans', 'locations', 'selectedRental'));
    }

    // Show the form for creating a new resource.
    public function create()
    {
        // Verify role handled by middleware
        return view('kendaraan.manage', ['kendaraan' => null]); 
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'merk_kendaraan' => 'required|string',
            'tipe_kendaraan' => 'required|string',
            'plat_nomor' => 'required|string|unique:kendaraan_listriks',
            'harga_perjam' => 'required|numeric',
            'gambar_kendaraan' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('gambar_kendaraan')) {
            $path = $request->file('gambar_kendaraan')->store('kendaraan', 'public');
            $validated['gambar_kendaraan'] = $path;
        }

        $kendaraan = M_KendaraanListrik::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Kendaraan created successfully', 'data' => $kendaraan], 201);
        }

        return redirect()->route('manage.kendaraan')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    // Display the specified resource.
    public function show($id)
    {
        $kendaraan = M_KendaraanListrik::findOrFail($id);
        
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json($kendaraan);
        }

        return view('kendaraan.detail', compact('kendaraan'));
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        $kendaraan = M_KendaraanListrik::findOrFail($id);
        return view('kendaraan.manage', compact('kendaraan'));
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $kendaraan = M_KendaraanListrik::findOrFail($id);

        $validated = $request->validate([
            'merk_kendaraan' => 'required|string',
            'tipe_kendaraan' => 'required|string',
            'plat_nomor' => 'required|string|unique:kendaraan_listriks,plat_nomor,'.$id.',id_kendaraan',
            'harga_perjam' => 'required|numeric',
            'status_ketersediaan' => 'required|in:tersedia,disewa,perbaikan',
            'gambar_kendaraan' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('gambar_kendaraan')) {
            // Delete old image
            if ($kendaraan->gambar_kendaraan) {
                Storage::disk('public')->delete($kendaraan->gambar_kendaraan);
            }
            $path = $request->file('gambar_kendaraan')->store('kendaraan', 'public');
            $validated['gambar_kendaraan'] = $path;
        }

        $kendaraan->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Kendaraan updated successfully', 'data' => $kendaraan]);
        }

        return redirect()->route('manage.kendaraan')->with('success', 'Kendaraan berhasil diupdate.');
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $kendaraan = M_KendaraanListrik::findOrFail($id);
        if ($kendaraan->gambar_kendaraan) {
            Storage::disk('public')->delete($kendaraan->gambar_kendaraan);
        }
        $kendaraan->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Kendaraan deleted successfully']);
        }

        return redirect()->route('manage.kendaraan')->with('success', 'Kendaraan berhasil dihapus.');
    }

    // Manage Page (Admin/Owner view)
    public function manage()
    {
        $kendaraans = M_KendaraanListrik::all();
        return view('kendaraan.index_manage', compact('kendaraans'));
    }
}
