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

    <!-- Review section removed: reviews are now rental-level and not shown per-vehicle on booking page -->
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
