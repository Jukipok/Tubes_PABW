@extends('layouts.main')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <style>
        #map {
            height: 100%;
            width: 100%;
            z-index: 10;
        }
    </style>
@endpush

@section('title', 'Katalog Kendaraan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Berhasil!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    <div class="text-center mb-12">
        <h1 class="text-3xl font-extrabold text-gray-900">Electric Vehicle Rent</h1>
        <p class="text-gray-500 mt-2">Jelajahi berbagai tempat dengan pengalaman berkendara kendaraan listrik yang aman dan terjangkau.</p>
        @if(isset($selectedRental))
            <div class="mt-4 bg-blue-50 border-l-4 border-blue-500 p-4 flex justify-between items-center animate-pulse-once">
                <div>
                    <p class="text-blue-700 font-bold">Menampilkan kendaraan di: {{ $selectedRental->nama_rental }}</p>
                    <p class="text-xs text-blue-600">{{ $selectedRental->lokasi_rental }}</p>
                </div>
                <a href="{{ route('katalog') }}" class="text-sm bg-white text-blue-600 px-3 py-1 rounded shadow hover:bg-gray-50 transition">
                    Lihat Semua Lokasi
                </a>
            </div>
        @endif
    </div>

    <!-- Map Section -->
    <div class="mb-12 rounded-lg shadow-lg border border-gray-200 overflow-hidden bg-white">
        <div class="flex flex-col md:flex-row h-[600px]">
            <!-- Panel (Side-by-side on desktop, top on mobile) -->
            <div class="w-full md:w-96 bg-white p-6 border-b md:border-b-0 md:border-r border-gray-200 overflow-y-auto">
                <h2 class="text-xl font-bold text-blue-700 mb-1">Temukan Rental Terdekat</h2>
                <p class="text-gray-500 text-sm mb-5">Jelajahi lokasi penyewaan kendaraan listrik di sekitar Anda.</p>

                <!-- Search -->
                <div class="relative mb-4">
                    <input type="text" placeholder="Cari lokasi..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2 mb-6">
                    <button onclick="locateUser()" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Lokasi Saya
                    </button>
                    <button onclick="resetMap()" class="bg-white border border-gray-200 text-gray-600 p-2 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </div>

                <!-- List -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">LOKASI POPULER</h3>
                    <div class="space-y-3">
                        @forelse($locations ?? [] as $loc)
                        <div onclick="focusLocation({{ $loc['lat'] }}, {{ $loc['lng'] }})" class="flex items-start gap-3 p-3 rounded-xl hover:bg-blue-50 cursor-pointer transition-colors group">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">{{ $loc['name'] }}</h4>
                                <p class="text-xs text-gray-500">{{ $loc['address'] }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 italic">Tidak ada data lokasi.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div id="map" class="flex-1 w-full bg-gray-100 relative z-0"></div>
        </div>
    </div>

    <!-- Search/Filter -->
    <div class="mb-8 flex justify-center">
        <form action="{{ route('katalog') }}" method="GET" class="flex gap-2 w-full max-w-md">
            <input type="text" name="search" placeholder="Cari merk atau tipe..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border" value="{{ request('search') }}">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">Cari</button>
        </form>
    </div>



    <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-3 xl:gap-x-8">
        @foreach($kendaraans as $car)
        <div class="group relative bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="w-full min-h-60 bg-gray-200 aspect-w-1 aspect-h-1 rounded-md overflow-hidden group-hover:opacity-75 lg:h-60 lg:aspect-none">
                <img src="{{ $car->gambar_kendaraan ? asset('storage/' . $car->gambar_kendaraan) : 'https://via.placeholder.com/300x200?text=No+Image' }}" alt="{{ $car->merk_kendaraan }}" class="w-full h-full object-center object-cover lg:w-full lg:h-full">
            </div>
            <div class="p-4">
                <h3 class="text-lg font-bold text-gray-900">
                    <a href="#">
                        <span aria-hidden="true" class="absolute inset-0"></span>
                        {{ $car->merk_kendaraan }} {{ $car->tipe_kendaraan }}
                    </a>
                </h3>
                <p class="mt-1 text-sm text-gray-500">{{ $car->plat_nomor }}</p>
                <div class="flex justify-between items-center mt-4">
                    <p class="text-lg font-medium text-blue-600 uppercase">Rp {{ number_format($car->harga_perjam, 0, ',', '.') }} / jam</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $car->status_ketersediaan == 'tersedia' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($car->status_ketersediaan) }}
                    </span>
                </div>
            </div>
            @if($car->status_ketersediaan == 'tersedia')
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <a href="{{ route('booking.create', $car->id_kendaraan) }}" class="w-full block text-center bg-blue-600 border border-transparent rounded-md py-2 px-4 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 z-10 relative mb-2">
                    Sewa Sekarang
                </a>
                
                <!-- Xendit Demo Trigger Removed -->
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Map
            const map = L.map('map', {zoomControl: false}).setView([-6.2088, 106.8456], 10);
            L.control.zoom({ position: 'bottomright' }).addTo(map);
            
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // PHP Data to JS
            const locations = @json($locations ?? []);
            let userMarker = null;

            // Add Markers
            locations.forEach(loc => {
                const marker = L.marker([loc.lat, loc.lng]).addTo(map);
                
                const markers = '⭐'.repeat(Math.round(loc.rating || 0));
                const ratingDisplay = loc.rating 
                    ? `<div class="text-yellow-500 text-xs mb-1">${markers} <span class="text-gray-600">(${loc.reviews})</span></div>` 
                    : `<div class="text-gray-400 text-xs mb-1">Belum ada ulasan</div>`;

                const popupContent = `
                    <div class="p-2 min-w-[200px]">
                        <h3 class="font-bold text-gray-900 text-sm mb-1">${loc.name}</h3>
                        ${ratingDisplay}
                        <p class="text-xs text-gray-500 mb-3">${loc.address}</p>
                        <a href="?rental_id=${loc.id}" class="block w-full text-center bg-blue-600 text-white text-xs font-bold py-2 rounded hover:bg-blue-700 transition">
                            Lihat Kendaraan
                        </a>
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                
                marker.on('click', function() {
                    map.flyTo([loc.lat, loc.lng], 14);
                });
            });

            // Adjust map view to fit markers
            function fitToMarkers() {
                if (locations.length > 0) {
                    const bounds = locations.map(loc => [loc.lat, loc.lng]);
                    map.fitBounds(bounds, { padding: [50, 50] });
                }
            }
            fitToMarkers();

            // Expose functions to global scope for onclick handlers
            window.focusLocation = function(lat, lng) {
                map.flyTo([lat, lng], 16);
            };

            window.resetMap = function() {
                fitToMarkers();
                if (userMarker) {
                    map.removeLayer(userMarker);
                    userMarker = null;
                }
            };

            window.locateUser = function() {
                if (!navigator.geolocation) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Geolocation tidak didukung oleh browser Anda.',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Mencari lokasi...',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                navigator.geolocation.getCurrentPosition((position) => {
                    Swal.close();
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    if (userMarker) {
                        map.removeLayer(userMarker);
                    }

                    // Custom User Icon usually red or different
                    userMarker = L.marker([lat, lng], {
                        icon: L.divIcon({
                            className: 'bg-transparent',
                            html: '<div class="w-4 h-4 bg-red-500 rounded-full border-2 border-white shadow-lg ring-4 ring-red-500/30"></div>'
                        })
                    }).addTo(map).bindPopup("Lokasi Anda").openPopup();

                    map.flyTo([lat, lng], 15);

                }, (error) => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Tidak dapat mengambil lokasi Anda. Pastikan GPS aktif.',
                    });
                });
            };
        });
    </script>
@endpush
