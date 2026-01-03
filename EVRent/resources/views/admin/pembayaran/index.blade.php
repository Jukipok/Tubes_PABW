@extends('layouts.main')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">{{ session('success') }}</div>
    @endif

    <h1 class="text-2xl font-bold mb-4">Pembayaran Masuk</h1>

    @if($payments->isEmpty())
        <div class="bg-white p-6 rounded shadow text-center text-gray-600">Tidak ada pembayaran yang menunggu verifikasi.</div>
    @else
        <div class="bg-white shadow rounded overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemesanan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($payments as $pay)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pay->id_pembayaran }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">#{{ $pay->id_pemesanan }} - {{ $pay->pemesanan->kendaraan->merk_kendaraan ?? '' }} {{ $pay->pemesanan->kendaraan->tipe_kendaraan ?? '' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $pay->pemesanan->pelanggan->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Rp {{ number_format($pay->jumlah_bayar ?? $pay->pemesanan->total_biaya ?? 0, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if($pay->bukti_transfer)
                                <a href="{{ asset('storage/' . $pay->bukti_transfer) }}" target="_blank" class="text-blue-600 underline">Lihat Bukti</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <form action="{{ route('admin.pembayaran.verify', $pay->id_pembayaran) }}" method="POST" onsubmit="return confirm('Verifikasi pembayaran ini?')">
                                @csrf
                                <button type="submit" class="px-3 py-1 rounded bg-green-600 text-white hover:bg-green-700">Verifikasi</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
