<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\SupplierController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'submitLogin'])->name('login.submit');
});

Route::middleware(['auth'])->group(function () {
    // Protected routes go here
    Route::group(['prefix' => 'admin'], function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::resource('locations', LocationController::class);
        Route::resource('suppliers', SupplierController::class);
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

