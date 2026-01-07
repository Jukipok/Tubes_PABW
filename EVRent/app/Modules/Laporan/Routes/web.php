<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Laporan\Controllers\C_Laporan;

Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:pelanggan'])->group(function () {
        Route::get('/booking/{id}/lapor', [C_Laporan::class, 'create'])->name('laporan.create');
        Route::post('/laporan', [C_Laporan::class, 'store'])->name('laporan.store');
    });
});
