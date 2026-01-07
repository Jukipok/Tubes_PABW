<?php

use Illuminate\Support\Facades\Route;




// Modules Controllers imported in their respective routes




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth Routes
// Auth Routes moved to Modules/Auth/Routes/web.php

// Kendaraan Routes moved to Modules/Kendaraan/Routes/web.php

Route::middleware(['auth'])->group(function () {
    // Admin Routes
    Route::middleware(['role:admin_evrent,admin_sewa'])->group(function () {
         Route::get('/admin/dashboard', function () { return view('dashboard.admin'); })->name('admin.dashboard');
            // Payment verification
// Transaksi Routes moved to Modules/Transaksi/Routes/web.php
    });

});
