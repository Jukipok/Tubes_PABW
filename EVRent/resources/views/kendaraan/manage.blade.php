@extends('layouts.main')

@section('title', isset($kendaraan) ? 'Edit Kendaraan' : 'Tambah Kendaraan')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6">{{ isset($kendaraan) ? 'Edit Kendaraan' : 'Tambah Kendaraan Baru' }}</h2>
        
        <form action="{{ isset($kendaraan) ? route('kendaraan.update', $kendaraan->id_kendaraan) : route('kendaraan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($kendaraan)) @method('PUT') @endif

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Merk Kendaraan</label>
                    <input type="text" name="merk_kendaraan" value="{{ old('merk_kendaraan', $kendaraan->merk_kendaraan ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipe Kendaraan</label>
                    <input type="text" name="tipe_kendaraan" value="{{ old('tipe_kendaraan', $kendaraan->tipe_kendaraan ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Plat Nomor</label>
                    <input type="text" name="plat_nomor" value="{{ old('plat_nomor', $kendaraan->plat_nomor ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Harga Per Jam</label>
                    <input type="number" name="harga_perjam" value="{{ old('harga_perjam', $kendaraan->harga_perjam ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2">
                </div>

                @if(isset($kendaraan))
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status_ketersediaan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2">
                        <option value="tersedia" {{ $kendaraan->status_ketersediaan == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="disewa" {{ $kendaraan->status_ketersediaan == 'disewa' ? 'selected' : '' }}>Disewa</option>
                        <option value="perbaikan" {{ $kendaraan->status_ketersediaan == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Gambar Kendaraan</label>
                    @if(isset($kendaraan) && $kendaraan->gambar_kendaraan)
                        <img src="{{ asset('storage/'.$kendaraan->gambar_kendaraan) }}" class="h-20 w-auto mb-2 rounded">
                    @endif
                    <input type="file" name="gambar_kendaraan" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('manage.kendaraan') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
