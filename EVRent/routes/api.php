<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Modules\Auth\Controllers\Api\AuthApiController;
use App\Modules\Kendaraan\Controllers\Api\KendaraanApiController;
use App\Modules\Transaksi\Controllers\Api\TransaksiApiController;
use App\Modules\Laporan\Controllers\Api\LaporanApiController;
use App\Modules\Pembayaran\Controllers\Api\XenditWebhookController;
use App\Modules\Pembayaran\Controllers\Api\PaymentApiController; // New Import
use App\Modules\Transaksi\Controllers\Api\DashboardApiController; // New Import

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Public Routes ---

// Auth
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);

// Kendaraan (Catalog)
Route::get('/kendaraan', [KendaraanApiController::class, 'index']);
Route::get('/kendaraan/{id}', [KendaraanApiController::class, 'show']);

// Xendit Webhook
Route::post('/xendit/webhook', [XenditWebhookController::class, 'handleInvoice']);


// --- Protected Routes (Requires Login) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Profile
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', [AuthApiController::class, 'userProfile']);
    Route::put('/user', [AuthApiController::class, 'updateProfile']); // Edit Profile

    // Transaksi (Booking)
    Route::post('/booking', [TransaksiApiController::class, 'store']);
    Route::get('/booking/history', [TransaksiApiController::class, 'history']);
    Route::get('/booking/{id}', [TransaksiApiController::class, 'show']);
    Route::delete('/booking/{id}', [TransaksiApiController::class, 'destroy']); // Cancel booking

    // Payment API
    Route::post('/payment/create-invoice', [PaymentApiController::class, 'createInvoice']);

    // Dashboard API (Owner Stats)
    Route::get('/dashboard/owner-stats', [DashboardApiController::class, 'ownerStats']);

    // Kendaraan Management (Create/Edit/Delete)
    Route::post('/kendaraan', [KendaraanApiController::class, 'store']);
    Route::put('/kendaraan/{id}', [KendaraanApiController::class, 'update']);
    Route::delete('/kendaraan/{id}', [KendaraanApiController::class, 'destroy']);
    
    // Ulasan
    Route::post('/ulasan', [LaporanApiController::class, 'store']);
    Route::get('/ulasan', [LaporanApiController::class, 'index']); // Can be public too if needed

});
