<?php

use Illuminate\Support\Facades\Route;






Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:admin_evrent,admin_sewa'])->group(function () {
         Route::get('/admin/dashboard', function () { return view('dashboard.admin'); })->name('admin.dashboard');
    });

});
