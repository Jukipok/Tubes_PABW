<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\C_Auth;
use App\Http\Controllers\C_Kendaraan;
use App\Http\Controllers\C_Transaksi;
use App\Http\Controllers\C_Xendit;

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
Route::get('/login', [C_Auth::class, 'viewLogin'])->name('login');
Route::post('/login', [C_Auth::class, 'login'])->name('login.post');
Route::get('/register', [C_Auth::class, 'viewRegister'])->name('register');
Route::post('/register', [C_Auth::class, 'register'])->name('register.post');
Route::post('/logout', [C_Auth::class, 'logout'])->name('logout');

// Public Home (Landing Page - Catalog usually)
Route::get('/', [C_Kendaraan::class, 'index'])->name('home');

// Dashboard redirects based on Role (handled in Controller, but specific routes here)
Route::middleware(['auth'])->group(function () {
    

// ... moved imports to top


    // Pelanggan Routes
    Route::middleware(['role:pelanggan'])->group(function () {
        // Route::get('/katalog', function () { return 'Katalog Page'; })->name('katalog'); // REPLACED
        Route::get('/katalog', [C_Kendaraan::class, 'index'])->name('katalog');
        Route::get('/booking/{id}', [C_Transaksi::class, 'create'])->name('booking.create');
        Route::post('/booking', [C_Transaksi::class, 'store'])->name('booking.store');
        Route::get('/booking/{id}/pembayaran', [C_Transaksi::class, 'createPayment'])->name('pembayaran.create');
        Route::post('/booking/{id}/pembayaran', [C_Transaksi::class, 'processPayment'])->name('pembayaran.process');
        Route::get('/my-bookings', [C_Transaksi::class, 'history'])->name('my_bookings');
        Route::post('/ulasan', [C_Transaksi::class, 'storeReview'])->name('ulasan.store');
        Route::get('/lokasi', function () { return view('kendaraan.lokasi'); })->name('lokasi');

        // Xendit Payment Routes (Demo)
        Route::get('/payment/{id}/create', [C_Xendit::class, 'createInvoice'])->name('payment.create');
        Route::get('/payment/success', [C_Xendit::class, 'success'])->name('payment.success');
    });

    // Admin & Owner Routes (Shared for Manage Kendaraan? Or just Owner? Diagram says both use same Models, maybe Admin manages too?)
    // UML: Admin Sewa & Pemilik Rental both seem to share access or separated. 
    // Usually Owner manages vehicles. Admin manages Rent/Orders. 
    // Let's allow both or just Owner for Vehicle Management as per typical logic?
    // Diagram: M_KendaraanListrik linked to M_PemilikRental? No direct link drawn, but implied ownership.
    // I'll allow both roles to access Manage Kendaraan for simplicity or just Pemilik. 
    // Let's add it to a shared group or specific.
    
    Route::middleware(['role:pemilik_rental,admin_evrent'])->group(function () {
        Route::get('/manage/kendaraan', [C_Kendaraan::class, 'manage'])->name('manage.kendaraan');
        Route::get('/manage/kendaraan/create', [C_Kendaraan::class, 'create'])->name('kendaraan.create');
        Route::post('/manage/kendaraan', [C_Kendaraan::class, 'store'])->name('kendaraan.store');
        Route::get('/manage/kendaraan/{id}/edit', [C_Kendaraan::class, 'edit'])->name('kendaraan.edit');
        Route::put('/manage/kendaraan/{id}', [C_Kendaraan::class, 'update'])->name('kendaraan.update');
        Route::delete('/manage/kendaraan/{id}', [C_Kendaraan::class, 'destroy'])->name('kendaraan.destroy');
        Route::get('/owner/dashboard', function () { return view('dashboard.owner'); })->name('owner.dashboard'); // Update redirect
    });

    // Admin Routes
    Route::middleware(['role:admin_evrent,admin_sewa'])->group(function () {
         Route::get('/admin/dashboard', function () { return view('dashboard.admin'); })->name('admin.dashboard');
    });

});
