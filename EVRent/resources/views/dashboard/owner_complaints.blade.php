@extends('layouts.main')

@section('title', 'Laporan Masalah Kendaraan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Laporan Masalah Kendaraan</h1>
        <a href="{{ route('owner.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">&larr; Kembali ke Dashboard</a>
    </div>

    @if($reports->isEmpty())
        <div class="bg-white shadow-sm rounded-xl p-12 text-center border border-gray-100">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 text-red-500 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900">Belum ada laporan</h3>
            <p class="mt-2 text-sm text-gray-500">Kendaraan Anda aman! Belum ada masalah yang dilaporkan.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($reports as $report)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <!-- Header: User & Vehicle -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-gradient-to-br from-red-100 to-pink-100 text-red-600 font-bold text-lg shadow-sm">
                                    {{ substr($report->pemesanan->pelanggan->user->name ?? 'U', 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $report->pemesanan->pelanggan->user->name ?? 'Pengguna' }}</h3>
                                <div class="mt-1 flex flex-col space-y-1 sm:flex-row sm:space-y-0 sm:space-x-4 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ $report->pemesanan->kendaraan->merk_kendaraan ?? '-' }} ({{ $report->pemesanan->kendaraan->plat_nomor ?? '-' }})
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ $report->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $report->status_laporan == 'pending' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : 'bg-green-100 text-green-800 border border-green-200' }}">
                            @if($report->status_laporan == 'pending')
                                <svg class="mr-1.5 h-2 w-2 text-yellow-500 fill-current" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                            @else
                                <svg class="mr-1.5 h-2 w-2 text-green-500 fill-current" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                            @endif
                            {{ ucfirst($report->status_laporan) }}
                        </span>
                    </div>

                    <!-- Report Content -->
                    <div class="mt-4 pl-16">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide text-xs mb-2">
                                {{ ucwords(str_replace('_', ' ', $report->jenis_laporan)) }}
                            </h4>
                            <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">"{{ $report->deskripsi_masalah }}"</p>
                            
                            @if($report->foto_bukti)
                                <div class="mt-4">
                                    <p class="text-xs font-medium text-gray-500 mb-2 uppercase">Bukti Foto</p>
                                    <a href="{{ asset('storage/'.$report->foto_bukti) }}" target="_blank" class="inline-block group relative">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-opacity rounded-lg"></div>
                                        <img src="{{ asset('storage/'.$report->foto_bukti) }}" alt="Bukti Laporan" class="h-32 w-auto rounded-lg border border-gray-200 shadow-sm transition-transform duration-300 transform group-hover:scale-105">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
