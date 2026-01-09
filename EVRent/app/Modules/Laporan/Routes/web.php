<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Laporan\Controllers\C_Laporan;

Route::middleware(['auth'])->group(function () {

    Route::middleware(['role:pelanggan'])->group(function () {
        Route::get('/booking/{id}/lapor', [C_Laporan::class, 'create'])->name('laporan.create');
        Route::post('/laporan', [C_Laporan::class, 'store'])->name('laporan.store');
    });


    Route::middleware(['role:admin_evrent,admin_sewa'])->group(function () {
        Route::get('/admin/laporan/masalah', [C_Laporan::class, 'adminReports'])->name('admin.laporan.masalah');
    });


    Route::middleware(['role:pemilik_rental,admin_evrent'])->group(function () {
        Route::get('/owner/laporan-masalah', [C_Laporan::class, 'ownerReports'])->name('owner.complaints');
    });
});
