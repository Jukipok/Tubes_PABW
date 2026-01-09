<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Modules\Auth\Controllers\Api\AuthApiController;
use App\Modules\Kendaraan\Controllers\Api\KendaraanApiController;
use App\Modules\Transaksi\Controllers\Api\TransaksiApiController;
use App\Modules\Laporan\Controllers\Api\LaporanApiController;
use App\Modules\Pembayaran\Controllers\Api\XenditWebhookController;
use App\Modules\Pembayaran\Controllers\Api\PaymentApiController;
use App\Modules\Transaksi\Controllers\Api\DashboardApiController;




Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);


Route::get('/kendaraan', [KendaraanApiController::class, 'index']);
Route::get('/kendaraan/{id}', [KendaraanApiController::class, 'show']);


Route::post('/xendit/webhook', [XenditWebhookController::class, 'handleInvoice']);


Route::middleware('auth:sanctum')->group(function () {
    

    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', [AuthApiController::class, 'userProfile']);
    Route::put('/user', [AuthApiController::class, 'updateProfile']);
    Route::post('/booking', [TransaksiApiController::class, 'store']);
    Route::get('/booking/history', [TransaksiApiController::class, 'history']);
    Route::get('/booking/{id}', [TransaksiApiController::class, 'show']);
    Route::post('/booking/return/{id}', [TransaksiApiController::class, 'returnVehicle']);
    Route::delete('/booking/{id}', [TransaksiApiController::class, 'destroy']);
    Route::get('/owner/transaksi', [TransaksiApiController::class, 'ownerHistory']);

    Route::get('/dashboard/owner-stats', [DashboardApiController::class, 'ownerStats']);
    Route::post('/kendaraan', [KendaraanApiController::class, 'store']);
    Route::put('/kendaraan/{id}', [KendaraanApiController::class, 'update']);
    Route::delete('/kendaraan/{id}', [KendaraanApiController::class, 'destroy']);
    
    Route::delete('/kendaraan/{id}', [KendaraanApiController::class, 'destroy']);
    Route::post('/ulasan', [LaporanApiController::class, 'store']);
    Route::get('/ulasan', [LaporanApiController::class, 'index']);


    Route::post('/laporan', [LaporanApiController::class, 'storeComplaint']); 
    Route::get('/admin/laporan', [LaporanApiController::class, 'adminComplaints']);
    Route::get('/owner/laporan', [LaporanApiController::class, 'ownerComplaints']);

});
