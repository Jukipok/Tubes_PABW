@extends('layouts.main')

@section('title', 'Owner Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Dashboard Pemilik Rental</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="bg-white shadow rounded-lg p-6 flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Pendapatan</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white shadow rounded-lg p-6 flex items-center">
             <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Transaksi Aktif</p>
                <p class="text-2xl font-bold text-gray-900">{{ $transaksiAktif }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Chart Section -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Statistik Pendapatan (6 Bulan Terakhir)</h3>
            <canvas id="revenueChart"></canvas>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Aksi Cepat</h3>
            <ul class="space-y-4">
                <li>
                    <a href="{{ route('manage.kendaraan') }}" class="flex items-center p-3 text-base font-bold text-gray-900 bg-gray-50 rounded-lg hover:bg-gray-100 group hover:shadow">
                        <span class="flex-1 ml-3 whitespace-nowrap">Kelola Armada Saya</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('owner.financial') }}" class="flex items-center p-3 text-base font-bold text-gray-900 bg-gray-50 rounded-lg hover:bg-gray-100 group hover:shadow">
                        <span class="flex-1 ml-3 whitespace-nowrap">Laporan Keuangan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('owner.reviews') }}" class="flex items-center p-3 text-base font-bold text-gray-900 bg-gray-50 rounded-lg hover:bg-gray-100 group hover:shadow">
                        <span class="flex-1 ml-3 whitespace-nowrap">Lihat Ulasan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('owner.complaints') }}" class="flex items-center p-3 text-base font-bold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 group hover:shadow">
                        <span class="flex-1 ml-3 whitespace-nowrap">Laporan Masalah</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const labels = @json($labels);
    const revenueData = @json($revenueData);

    const revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rupiah)',
                data: revenueData,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.2)',
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                     ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                         label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
