@extends('layouts.main')

@section('title', 'Daftar Ulasan Pengguna')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Ulasan Pengguna</h1>
        <a href="{{ route('owner.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">&larr; Kembali ke Dashboard</a>
    </div>

    @if($reviews->isEmpty())
        <div class="bg-white shadow rounded-lg p-10 text-center">
            <p class="text-gray-500">Belum ada ulasan dari pengguna untuk rental Anda.</p>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($reviews as $review)
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                        {{ substr($review->pelanggan->user->name ?? 'User', 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $review->pelanggan->user->name ?? 'Pengguna' }}</h3>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <span class="mr-2">Rating:</span>
                                        @for($i = 0; $i < $review->rating; $i++)
                                            <span class="text-yellow-400">★</span>
                                        @endfor
                                        @for($i = $review->rating; $i < 5; $i++)
                                            <span class="text-gray-300">★</span>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $review->created_at->format('d M Y') }}
                            </div>
                        </div>
                        <div class="mt-2">
                            <!-- Removed 'Ke Rental' line since owner only sees their own -->
                            <p class="mt-2 text-gray-800 text-sm italic">"{{ $review->komentar }}"</p>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
