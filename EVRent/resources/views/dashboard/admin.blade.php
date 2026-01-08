@extends('layouts.main')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Pengguna</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ \App\Modules\Auth\Models\M_User::where('role', 'pelanggan')->count() }}</dd>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Kendaraan</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ \App\Modules\Kendaraan\Models\M_KendaraanListrik::count() }}</dd>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Pendapatan</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">
                    Rp {{ number_format(\App\Modules\Pembayaran\Models\M_XenditPayment::where('status', 'PAID')->sum('amount'), 0, ',', '.') }}
                </dd>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Pengeluaran</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">
                    Rp {{ number_format(\App\Modules\Pembayaran\Models\M_XenditPayment::where('status', 'PAID')->count() * 4500, 0, ',', '.') }} <span class="text-xs text-gray-400 font-normal">(Est. Fee)</span>
                </dd>
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
                    <a href="{{ route('admin.ulasan.index') }}" class="block px-4 py-2 border rounded-md hover:bg-gray-50 text-blue-600 font-medium">
                        Lihat Ulasan
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.laporan.xendit') }}" class="block px-4 py-2 border rounded-md hover:bg-gray-50 text-blue-600 font-medium">
                        Laporan Pembayaran
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.laporan.masalah') }}" class="block px-4 py-2 border rounded-md hover:bg-gray-50 text-red-600 font-medium">
                        Laporan Masalah Pengguna
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
