@extends('layouts.main')

@section('title', 'Pembayaran')

@section('content')
<div class="max-w-md mx-auto py-12 px-4">
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-green-600">
            <h2 class="text-2xl font-bold text-white text-center">Konfirmasi Pembayaran</h2>
        </div>
        <div class="p-6 space-y-6">
            <div class="text-center">
                <p class="text-gray-600">Total Tagihan:</p>
                <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($pemesanan->total_biaya, 0, ',', '.') }}</p>
            </div>

            <div class="bg-gray-50 p-4 rounded text-sm text-gray-700">
                <p class="font-bold">Instruksi Pembayaran:</p>
                <p>Silakan transfer ke rekening berikut:</p>
                <ul class="list-disc pl-5 mt-2">
                    <li>BCA: 1234567890 (PT. Rental Indonesia)</li>
                    <li>Mandiri: 0987654321 (PT. Rental Indonesia)</li>
                </ul>
            </div>

            <form action="{{ route('pembayaran.process', $pemesanan->id_pemesanan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="w-full px-3 py-2 border rounded-lg">
                        <option value="transfer_bank">Transfer Bank</option>
                        <option value="ewallet">E-Wallet</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">Upload Bukti Transfer</label>
                    <input type="file" name="bukti_transfer" required class="w-full px-3 py-2 border rounded-lg">
                </div>

                <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 transition">
                    Kirim Bukti Pembayaran
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
