<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Kendaraan\Controllers\C_Kendaraan;


Route::get('/', [C_Kendaraan::class, 'index'])->name('home');

use App\Modules\Transaksi\Controllers\C_Transaksi;

Route::middleware(['auth'])->group(function () {

    Route::middleware(['role:pelanggan'])->group(function () {
        Route::get('/katalog', [C_Kendaraan::class, 'index'])->name('katalog');
        Route::get('/lokasi', function () { return view('kendaraan.lokasi'); })->name('lokasi');
    });

    Route::middleware(['role:pemilik_rental,admin_evrent'])->group(function () {
        Route::get('/manage/kendaraan', [C_Kendaraan::class, 'manage'])->name('manage.kendaraan');
        Route::get('/manage/kendaraan/create', [C_Kendaraan::class, 'create'])->name('kendaraan.create');
        Route::post('/manage/kendaraan', [C_Kendaraan::class, 'store'])->name('kendaraan.store');
        Route::get('/manage/kendaraan/{id}/edit', [C_Kendaraan::class, 'edit'])->name('kendaraan.edit');
        Route::put('/manage/kendaraan/{id}', [C_Kendaraan::class, 'update'])->name('kendaraan.update');
        Route::delete('/manage/kendaraan/{id}', [C_Kendaraan::class, 'destroy'])->name('kendaraan.destroy');
        Route::get('/owner/dashboard', [C_Transaksi::class, 'ownerDashboard'])->name('owner.dashboard'); 
        Route::get('/owner/laporan-keuangan', [C_Transaksi::class, 'ownerFinancialReport'])->name('owner.financial'); 
        Route::get('/owner/ulasan', [C_Transaksi::class, 'ownerReviews'])->name('owner.reviews'); 
    });
});
