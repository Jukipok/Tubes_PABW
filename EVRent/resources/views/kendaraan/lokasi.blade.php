@extends('layouts.main')

@section('title', 'Lokasi Rental - EVRent')

@section('content')
<div class="relative w-full h-screen overflow-hidden">
    <!-- Map Container -->
    <div id="map" class="absolute inset-0 z-0"></div>

    <!-- Floating Glass Panel -->
    <div class="absolute top-4 left-4 z-10 w-full max-w-sm p-4">
        <div class="glass rounded-2xl shadow-2xl p-6 backdrop-blur-md bg-white/70 border border-white/20">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                Temukan Rental Terdekat
            </h1>
            <p class="text-gray-600 text-sm mb-6">Jelajahi lokasi penyewaan kendaraan listrik di sekitar Anda.</p>

            <!-- Search Box -->
            <div class="relative group mb-6">
                <input type="text" id="search-location" 
                    class="w-full px-5 py-3 rounded-xl bg-white/50 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all pl-12 shadow-inner"
                    placeholder="Cari lokasi...">
                <svg class="w-6 h-6 text-gray-400 absolute left-3 top-3 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button onclick="locateUser()" 
                    class="flex-1 px-4 py-2 bg-gradient-to-tr from-blue-500 to-indigo-600 text-white rounded-lg shadow-lg hover:shadow-blue-500/30 hover:scale-105 transition-transform flex items-center justify-center gap-2 font-medium text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Lokasi Saya
                </button>
                <button onclick="resetMap()" 
                    class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-colors flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
            </div>

            <!-- List of Rentals (Scrollable) -->
            <div class="mt-8 space-y-4 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Lokasi Populer</h3>
                
                <!-- Item 1 -->
                <div class="group flex items-center gap-3 p-3 rounded-xl hover:bg-white/60 cursor-pointer transition-colors" onclick="flyToLocation(-6.2088, 106.8456, 'Kantor Pusat')">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">Kantor Pusat</h4>
                        <p class="text-xs text-gray-500">Jakarta, Indonesia</p>
                    </div>
                </div>

                <div class="group flex items-center gap-3 p-3 rounded-xl hover:bg-white/60 cursor-pointer transition-colors" onclick="flyToLocation(-6.9175, 107.6191, 'Cabang Bandung')">
                    <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center group-hover:bg-red-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">Cabang Bandung</h4>
                        <p class="text-xs text-gray-500">Jawa Barat, Indonesia</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
    .glass {
        background: rgba(255, 255, 255, 0.65);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.3);
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
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8; 
    }
    /* Hide Leaflet bottom controls on mobile if needed or style them */
    .leaflet-control-container .leaflet-routing-container-hide {
        display: none;
    }
</style>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
    // Initialize Map
    const map = L.map('map').setView([-6.2088, 106.8456], 13);
    let userMarker, routingControl;

    // Custom Tiles (Light Mode for Clean Look)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    // Custom Icons using SVG
    const createCustomIcon = (color) => {
        return L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color: ${color}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
    };

    // Current Locations (Mock Data)
    const locations = [
        { name: "Kantor Pusat", lat: -6.2088, lng: 106.8456, color: '#3B82F6' }, // Blue
        { name: "Cabang Bandung", lat: -6.9175, 107.6191, color: '#EF4444' }, // Red
    ];

    // Add Markers
    locations.forEach(loc => {
        L.marker([loc.lat, loc.lng], { icon: createCustomIcon(loc.color) })
            .addTo(map)
            .bindPopup(`<b>${loc.name}</b><br>Rental Kendaraan Listrik`)
            .on('click', function() {
                if (userMarker) {
                    calculateRoute(userMarker.getLatLng(), [loc.lat, loc.lng]);
                }
            });
    });

    // Locate User Function
    function locateUser() {
        if (!navigator.geolocation) {
            alert("Geolocation is not supported by your browser");
            return;
        }

        navigator.geolocation.getCurrentPosition((position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const userLatLng = [lat, lng];

            // Remove existing user marker
            if (userMarker) map.removeLayer(userMarker);

            // Add new user marker
            userMarker = L.marker(userLatLng, {
                icon: createCustomIcon('#EF4444') // Red for user
            }).addTo(map).bindPopup("Lokasi Anda").openPopup();

            map.flyTo(userLatLng, 15);
            
            // Auto route to nearest (Pusat for demo)
            calculateRoute(userLatLng, [-6.2088, 106.8456]);

        }, () => {
            alert("Gagal mendapatkan lokasi anda.");
        });
    }

    // Fly To Location Function
    function flyToLocation(lat, lng, name) {
        map.flyTo([lat, lng], 16);
    }

    // Routing Function
    function calculateRoute(start, end) {
        if (routingControl) {
            map.removeControl(routingControl);
        }

        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(start[0], start[1]),
                L.latLng(end[0], end[1])
            ],
            routeWhileDragging: false,
            showAlternatives: false,
            fitSelectedRoutes: true,
            lineOptions: {
                styles: [{ color: '#6366f1', opacity: 0.8, weight: 6 }]
            },
            createMarker: function() { return null; } // Don't create default markers
        }).addTo(map);
    }

    // Reset Map
    function resetMap() {
        if (routingControl) map.removeControl(routingControl);
        map.flyTo([-6.2088, 106.8456], 13);
    }
</script>
@endsection
