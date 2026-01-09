<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EVRent - Sewa Kendaraan Listrik</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 20px;
        }
    </style>
</head>
<body class="antialiased overflow-hidden m-0 p-0 w-screen h-screen">
    <!-- Map Container - Fixed position to ensure it covers everything -->
    <div id="map" class="fixed inset-0 w-full h-full z-0 bg-gray-200" style="width: 100vw; height: 100vh;"></div>

    <!-- Navbar / Header Overlay -->
    <div class="absolute top-0 w-full z-20 p-4 flex justify-between items-center pointer-events-none">
        <!-- Brand -->
        <div class="pointer-events-auto bg-white/90 backdrop-blur-sm px-4 py-2 rounded-full shadow-lg">
            <span class="font-bold text-xl bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">EVRent</span>
        </div>

        <!-- Auth Links -->
        @if (Route::has('login'))
            <div class="pointer-events-auto flex gap-2">
                @auth
                    <a href="{{ url('/katalog') }}" class="px-5 py-2 bg-blue-600 text-white rounded-full font-medium shadow-lg hover:bg-blue-700 transition-colors">
                        Katalog
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2 bg-white/90 text-gray-800 rounded-full font-medium shadow-lg hover:bg-white transition-colors">
                        Log in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-5 py-2 bg-blue-600 text-white rounded-full font-medium shadow-lg hover:bg-blue-700 transition-colors">
                            Register
                        </a>
                    @endif
                @endauth
            </div>
        @endif
    </div>

    <!-- Floating Glass Panel (Search & List) -->
    <div class="absolute top-20 left-4 z-10 w-full max-w-sm pointer-events-auto">
        <div class="glass rounded-2xl shadow-2xl p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                Temukan Rental Terdekat
            </h1>
            <p class="text-gray-600 text-sm mb-6">Pilih lokasi di peta untuk melakukan pemesanan.</p>

            <!-- Search Box -->
            <div class="relative group mb-4">
                <input type="text" id="search-location" 
                    class="w-full px-5 py-3 rounded-xl bg-white/50 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all pl-12 shadow-sm"
                    placeholder="Cari lokasi...">
                <svg class="w-6 h-6 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <!-- Action Button -->
            <button onclick="locateUser()" 
                class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Lokasi Saya
            </button>
            
            <!-- List based on passed data -->
            <div class="mt-6 space-y-3 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Cabang Tersedia</h3>
                @foreach($locations ?? [] as $loc)
                    <div class="group flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 cursor-pointer transition-colors border border-transparent hover:border-blue-100" 
                         onclick="focusLocation({{ $loc['lat'] }}, {{ $loc['lng'] }})">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">{{ $loc['name'] }}</h4>
                            <p class="text-xs text-gray-500 truncate w-40">{{ $loc['address'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Only Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

    <script>
        const map = L.map('map', {zoomControl: false}).setView([-6.2088, 106.8456], 13);
        

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        L.control.zoom({ position: 'bottomright' }).addTo(map);

        const locations = [
            @if(isset($locations))
                @foreach($locations as $loc)
                {
                    id: {{ $loc['id'] }},
                    name: "{{ $loc['name'] }}",
                    address: "{{ $loc['address'] }}",
                    lat: {{ $loc['lat'] }},
                    lng: {{ $loc['lng'] }},
                },
                @endforeach
            @endif
        ];

        locations.forEach(loc => {
            const marker = L.marker([loc.lat, loc.lng]).addTo(map);
            
            const marker = L.marker([loc.lat, loc.lng]).addTo(map);
                Swal.fire({
                    title: loc.name,
                    text: "Alamat: " + loc.address,
                    icon: 'info',
                    confirmButtonText: 'Lihat Katalog',
                    confirmButtonColor: '#2563EB',
                    showCancelButton: true,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ url('/katalog') }}";
                    }
                });
            });
        });

        function locateUser() {
            if (!navigator.geolocation) {
                alert("Geolocation tidak didukung browser ini.");
                return;
            }
            navigator.geolocation.getCurrentPosition((position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="background-color: #EF4444; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white;"></div>`
                    })
                }).addTo(map).bindPopup("Lokasi Anda").openPopup();

                map.flyTo([lat, lng], 14);
            });
        }

        function focusLocation(lat, lng) {
            map.flyTo([lat, lng], 16);
        }
    </script>
</body>
</html>
