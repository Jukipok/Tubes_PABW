@extends('layouts.main')

@section('title', 'Lapor Masalah Kendaraan')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-red-600">
            <h2 class="text-2xl font-bold text-white">Form Laporan Masalah</h2>
            <p class="text-red-100 text-sm">Laporkan kerusakan atau masalah pada kendaraan.</p>
        </div>
        <div class="p-6">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 p-4 bg-gray-50 rounded border border-gray-200">
                <p class="text-sm font-bold text-gray-500">Kendaraan:</p>
                <p class="text-lg text-gray-900">{{ $pemesanan->kendaraan->merk_kendaraan }} {{ $pemesanan->kendaraan->tipe_kendaraan }}</p>
                <p class="text-sm text-gray-600">Plat: {{ $pemesanan->kendaraan->plat_nomor }}</p>
                <p class="text-sm text-gray-600">ID Pesanan: #{{ $pemesanan->id_pemesanan }}</p>
            </div>

            <form action="{{ route('laporan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_pemesanan" value="{{ $pemesanan->id_pemesanan }}">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Jenis Laporan</label>
                    <select name="jenis_laporan" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Jenis Masalah --</option>
                        <option value="kerusakan_awal">Kerusakan Awal (Sebelum Penggunaan)</option>
                        <option value="kerusakan_akhir">Kerusakan Akhir (Setelah Penggunaan)</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Deskripsi Masalah</label>
                    <textarea name="deskripsi_masalah" rows="4" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Jelaskan detail kerusakan atau masalah yang ditemukan..."></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">Foto Bukti (Opsional)</label>
                    <input type="file" name="foto_bukti" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                </div>

                <button type="submit" class="w-full bg-red-600 text-white font-bold py-3 rounded-lg hover:bg-red-700 transition">
                    Kirim Laporan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
