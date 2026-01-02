<div class="bg-white rounded-lg shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105">
    <div class="h-48 bg-gray-200 relative">
        @if($kendaraan->gambar_kendaraan)
            <img src="{{ asset('storage/'.$kendaraan->gambar_kendaraan) }}" alt="{{ $kendaraan->merk_kendaraan }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        @endif
        <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-md text-xs font-bold">
            {{ ucfirst($kendaraan->status_ketersediaan) }}
        </div>
    </div>
    
    <div class="p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $kendaraan->merk_kendaraan }} {{ $kendaraan->tipe_kendaraan }}</h3>
        <div class="flex items-baseline mb-4">
            <span class="text-2xl font-bold text-blue-600">Rp {{ number_format($kendaraan->harga_perjam, 0, ',', '.') }}</span>
            <span class="text-gray-500 ml-1">/ hari</span>
        </div>
        
        <div class="space-y-2 mb-6">
            <div class="flex items-center text-gray-600 text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span>Electric Power</span>
            </div>
            <div class="flex items-center text-gray-600 text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Zero Emission</span>
            </div>
        </div>

        <a href="{{ route('booking.create', $kendaraan->id_kendaraan) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-bold py-3 rounded-lg transition duration-300">
            Sewa Sekarang
        </a>
    </div>
</div>
