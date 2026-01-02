@extends('layouts.main')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Pengguna</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ \App\Models\M_User::count() }}</dd>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Kendaraan</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ \App\Models\M_KendaraanListrik::count() }}</dd>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Pemesanan Aktif</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ \App\Models\M_Pemesanan::where('status_sewa', 'berlangsung')->count() }}</dd>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Menu Kelola</h3>
            <ul class="space-y-3">
                <li>
                    <a href="{{ route('manage.kendaraan') }}" class="block px-4 py-2 border rounded-md hover:bg-gray-50 text-blue-600 font-medium">
                        Kelola Kendaraan
                    </a>
                </li>
                <li>
                    <a href="#" class="block px-4 py-2 border rounded-md hover:bg-gray-50 text-blue-600 font-medium">
                        Verifikasi Pembayaran (Coming Soon)
                    </a>
                </li>
                <li>
                    <a href="#" class="block px-4 py-2 border rounded-md hover:bg-gray-50 text-blue-600 font-medium">
                        Laporan Penyewaan
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
