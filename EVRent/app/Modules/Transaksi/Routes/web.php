<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Transaksi\Controllers\C_Transaksi;

Route::middleware(['auth'])->group(function () {

    // Pelanggan Routes
    Route::middleware(['role:pelanggan'])->group(function () {
        Route::get('/booking/{id}', [C_Transaksi::class, 'create'])->name('booking.create');
        Route::post('/booking', [C_Transaksi::class, 'store'])->name('booking.store');
        Route::get('/booking/{id}/pembayaran', [C_Transaksi::class, 'createPayment'])->name('pembayaran.create');
        Route::post('/booking/{id}/pembayaran', [C_Transaksi::class, 'processPayment'])->name('pembayaran.process');
        Route::get('/my-bookings', [C_Transaksi::class, 'history'])->name('my_bookings');
        Route::post('/ulasan', [C_Transaksi::class, 'storeReview'])->name('ulasan.store');
        Route::post('/booking/{id}/return', [C_Transaksi::class, 'returnItem'])->name('booking.return');
        Route::delete('/booking/{id}', [C_Transaksi::class, 'deleteBooking'])->name('booking.delete');
    });

    // Admin Routes
    Route::middleware(['role:admin_evrent,admin_sewa'])->group(function () {
        // Payment verification
        Route::get('/admin/pembayaran', [C_Transaksi::class, 'adminPayments'])->name('admin.pembayaran.index');
        Route::post('/admin/pembayaran/{id}/verify', [C_Transaksi::class, 'verifyPayment'])->name('admin.pembayaran.verify');
        // Review list
        Route::get('/admin/ulasan', [C_Transaksi::class, 'adminReviews'])->name('admin.ulasan.index');
        // Xendit Report
        Route::get('/admin/laporan/xendit', [C_Transaksi::class, 'xenditReport'])->name('admin.laporan.xendit');
    });
});
