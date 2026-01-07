<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Pembayaran\Controllers\C_Xendit;

Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:pelanggan'])->group(function () {
        // Xendit Payment Routes
        Route::get('/payment/{id}/create', [C_Xendit::class, 'createInvoice'])->name('payment.create');
        Route::get('/payment/success', [C_Xendit::class, 'success'])->name('payment.success');
        Route::post('/payment/callback', [C_Xendit::class, 'callback'])->name('payment.callback');
    });
});
