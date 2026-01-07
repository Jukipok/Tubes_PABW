<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\C_Auth;

// Auth Routes
Route::get('/login', [C_Auth::class, 'viewLogin'])->name('login');
Route::post('/login', [C_Auth::class, 'login'])->name('login.post');
Route::get('/register', [C_Auth::class, 'viewRegister'])->name('register');
Route::post('/register', [C_Auth::class, 'register'])->name('register.post');
Route::post('/logout', [C_Auth::class, 'logout'])->name('logout');

use App\Modules\Auth\Controllers\ProfileController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('password', [C_Auth::class, 'updatePassword'])->name('password.update');
});
