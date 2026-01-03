@extends('layouts.main')

@section('title', 'Booking Kendaraan')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-blue-600">
            <h2 class="text-2xl font-bold text-white">Form Pemesanan</h2>
        </div>
        <div class="p-6">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="flex items-center mb-6">
                @if($kendaraan->gambar_kendaraan)
                    <img src="{{ asset('storage/'.$kendaraan->gambar_kendaraan) }}" class="h-24 w-32 object-cover rounded mr-4">
                @endif
                <div>
                    <h3 class="text-xl font-bold">{{ $kendaraan->merk_kendaraan }} {{ $kendaraan->tipe_kendaraan }}</h3>
                    <p class="text-gray-600">Rp {{ number_format($kendaraan->harga_perjam) }} / jam</p>
                </div>
            </div>

            <form action="{{ route('booking.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_kendaraan" value="{{ $kendaraan->id_kendaraan }}">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Tanggal Mulai Sewa</label>
                    <input type="datetime-local" name="tanggal_sewa" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Durasi Sewa (Jam)</label>
                    <input type="number" name="durasi_sewa" id="durasi_sewa" min="1" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="calculateTotal()">
                </div>

                <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                    <p class="text-lg font-bold">Total Biaya: <span id="total_biaya" class="text-blue-600">Rp 0</span></p>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition">
                    Lanjut ke Pembayaran
                </button>
            </form>
        </div>
        </div>
    </div>

    <!-- Review Section -->
    <div class="mt-8 bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-800">
            <h3 class="text-xl font-bold text-white">Ulasan Pengguna</h3>
        </div>
        <div class="p-6">
            @if($ulasans->isEmpty())
                <p class="text-gray-500 text-center">Belum ada ulasan untuk kendaraan ini.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach($ulasans as $ulasan)
                        <li class="py-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                        {{ substr($ulasan->pemesanan->pelanggan->user->name ?? 'User', 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $ulasan->pemesanan->pelanggan->user->name ?? 'Pengguna' }}
                                    </p>
                                    <div class="flex items-center">
                                        @for($i = 0; $i < 5; $i++)
                                            @if($i < $ulasan->rating)
                                                <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            @else
                                                <svg class="h-4 w-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            @endif
                                        @endfor
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ $ulasan->komentar }}</p>
                                    <p class="mt-1 text-xs text-gray-400">{{ $ulasan->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

<script>
    function calculateTotal() {
        const durasi = document.getElementById('durasi_sewa').value;
        const harga = {{ $kendaraan->harga_perjam }};
        const total = durasi * harga;
        document.getElementById('total_biaya').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }
</script>
@endsection
