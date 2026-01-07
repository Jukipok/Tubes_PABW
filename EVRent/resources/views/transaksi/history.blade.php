@extends('layouts.main')

@section('title', 'Riwayat Pesanan Saya')

@section('content')
<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-gray-900">Riwayat Pesanan</h2>
        <p class="mt-2 text-sm text-gray-600">Pantau status sewa kendaraan Anda di sini.</p>
    </div>

    @if($pemesanans->isEmpty())
        <div class="bg-white shadow rounded-lg p-10 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            <h3 class="text-lg font-medium text-gray-900">Belum ada pesanan</h3>
            <p class="text-gray-500 mb-6">Anda belum pernah menyewa kendaraan apa pun.</p>
            <a href="{{ route('katalog') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                Sewa Sekarang
            </a>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <ul class="divide-y divide-gray-200">
                @foreach($pemesanans as $pesanan)
                <li>
                    <div class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                            <!-- Vehicle Info -->
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-16 w-24 sm:h-12 sm:w-16 bg-gray-200 rounded overflow-hidden">
                                     @if($pesanan->gambar_kendaraan)
                                        <img src="{{ asset('storage/'.$pesanan->gambar_kendaraan) }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center text-xs text-gray-500">No Img</div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-bold text-blue-600 truncate">{{ $pesanan->merk_kendaraan }} {{ $pesanan->tipe_kendaraan }}</p>
                                    <p class="text-xs text-gray-500">Plat: {{ $pesanan->plat_nomor }}</p>
                                    <p class="text-xs text-gray-400 mt-1 sm:hidden">
                                        {{ \Carbon\Carbon::parse($pesanan->tanggal_sewa)->format('d M Y') }} • {{ $pesanan->durasi_sewa }} Jam
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Status & Price Mobile -->
                            <div class="flex justify-between items-center sm:hidden">
                                <div>
                                    @php
                                        // Status Classes (same as before)
                                        $statusClasses = [
                                            'menunggu_pembayaran' => 'bg-yellow-100 text-yellow-800',
                                            'menunggu_verifikasi' => 'bg-blue-100 text-blue-800',
                                            'dibayar' => 'bg-green-100 text-green-800',
                                            'selesai' => 'bg-gray-100 text-gray-800',
                                            'dibatalkan' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusLabel = [
                                            'menunggu_pembayaran' => 'Menunggu Pembayaran',
                                            'menunggu_verifikasi' => 'Verifikasi Pembayaran',
                                            'dibayar' => 'Aktif',
                                            'selesai' => 'Selesai',
                                            'dibatalkan' => 'Dibatalkan',
                                        ];
                                        $currentClass = $statusClasses[$pesanan->status_sewa] ?? 'bg-gray-100 text-gray-800';
                                        $currentLabel = $statusLabel[$pesanan->status_sewa] ?? ucfirst($pesanan->status_sewa);
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $currentClass }}">
                                        {{ $currentLabel }}
                                    </span>
                                </div>
                                <p class="text-gray-900 font-bold">Rp {{ number_format($pesanan->total_biaya) }}</p>
                            </div>

                            <!-- Desktop View Details -->
                            <div class="hidden sm:flex ml-2 flex-shrink-0">
                                    {{ $currentLabel }}
                                </span>
                                @if($pesanan->denda)
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Denda: Rp {{ number_format($pesanan->denda->total_denda) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Footer / Actions -->
                        <div class="mt-4 sm:flex sm:justify-between sm:items-center">
                            <div class="hidden sm:flex">
                                <p class="flex items-center text-sm text-gray-500 mr-6">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ \Carbon\Carbon::parse($pesanan->tanggal_sewa)->format('d M Y H:i') }}
                                </p>
                                <p class="flex items-center text-sm text-gray-500">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $pesanan->durasi_sewa }} Jam
                                </p>
                            </div>
                            <div class="flex items-center justify-end sm:mt-0 gap-4">
                                <p class="text-gray-900 font-bold mr-4 hidden sm:block">Rp {{ number_format($pesanan->total_biaya) }}</p>
                                
                                @if($pesanan->status_sewa == 'menunggu_pembayaran')
                                    <a href="{{ route('pembayaran.create', $pesanan->id_pemesanan) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200">
                                        Bayar &rarr;
                                    </a>
                                @elseif($pesanan->status_sewa == 'dibayar' || $pesanan->status_sewa == 'berlangsung')
                                    <form action="{{ route('booking.return', $pesanan->id_pemesanan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan kendaraan ini?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded text-purple-700 bg-purple-100 hover:bg-purple-200">
                                            Kembalikan
                                        </button>
                                    </form>
                                @elseif($pesanan->status_sewa == 'selesai')
                                    @php
                                        $pelangganId = Auth::user()->pelanggan->id_pelanggan ?? null;
                                        $reviewExists = \App\Modules\Laporan\Models\M_Ulasan::where('id_pemilik_rental', $pesanan->kendaraan->id_pemilik_rental ?? null)
                                            ->where('id_pelanggan', $pelangganId)
                                            ->exists();
                                    @endphp
                                    @if(!$reviewExists && $pesanan->kendaraan)
                                        <button onclick="openReviewModal({{ $pesanan->id_pemesanan }}, {{ $pesanan->kendaraan->id_pemilik_rental }})" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200">
                                            Beri Ulasan
                                        </button>
                                    @else
                                        <span class="text-green-600 text-sm font-medium">✔️ Sudah Diulas</span>
                                    @endif
                                @endif
                                
                                @if(in_array($pesanan->status_sewa, ['dibayar', 'berlangsung', 'selesai']))
                                    <a href="{{ route('laporan.create', $pesanan->id_pemesanan) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 ml-2">
                                        ! Lapor Masalah
                                    </a>
                                @endif
                                
                                @if($pesanan->status_sewa == 'menunggu_pembayaran' || $pesanan->status_sewa == 'selesai')
                                    <form action="{{ route('booking.delete', $pesanan->id_pemesanan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesanan ini? Tindakan ini tidak dapat dibatalkan.')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 ml-2">
                                            Hapus Pesanan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeReviewModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('ulasan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_pemesanan" id="modalBookingId">
                <input type="hidden" name="id_pemilik_rental" id="modalRentalId">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Beri Ulasan Rental</h3>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Rating</label>
                                <div class="flex items-center mt-1 space-x-1">
                                    <select name="rating" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="5">⭐⭐⭐⭐⭐ (Sangat Puas)</option>
                                        <option value="4">⭐⭐⭐⭐ (Puas)</option>
                                        <option value="3">⭐⭐⭐ (Biasa)</option>
                                        <option value="2">⭐⭐ (Kurang)</option>
                                        <option value="1">⭐ (Buruk)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Komentar</label>
                                <textarea name="komentar" rows="3" class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Tulis pengalaman Anda..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Kirim Ulasan
                    </button>
                    <button type="button" onclick="closeReviewModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openReviewModal(bookingId, rentalId) {
        document.getElementById('modalBookingId').value = bookingId;
        document.getElementById('modalRentalId').value = rentalId;
        document.getElementById('reviewModal').classList.remove('hidden');
    }
    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }
</script>
@endsection
