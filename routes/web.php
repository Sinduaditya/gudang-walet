<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Master\GradeCompanyController;
use App\Http\Controllers\Feature\GradingGoodsController;
use App\Http\Controllers\Master\GradeSupplierController;
use App\Http\Controllers\Feature\IncomingGoodsController;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/', [LoginController::class, 'submitLogin'])->name('login.submit');
});

Route::middleware(['auth'])->group(function () {
    // Protected routes go here
    Route::group(['prefix' => 'admin'], function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Incoming Goods Routes
        Route::prefix('incoming-goods')
            ->name('incoming-goods.')
            ->group(function () {
                //list all
                Route::get('/', [IncomingGoodsController::class, 'index'])->name('index');

                Route::get('export', [IncomingGoodsController::class, 'export'])->name('export');

                // Step 1
                Route::get('step-1', [IncomingGoodsController::class, 'createStep1'])->name('step1');
                Route::post('step-1', [IncomingGoodsController::class, 'storeStep1'])->name('store-step1');

                // Step 2
                Route::get('step-2', [IncomingGoodsController::class, 'createStep2'])->name('step2');
                Route::post('step-2', [IncomingGoodsController::class, 'storeStep2'])->name('store-step2');

                // Step 3
                Route::get('step-3', [IncomingGoodsController::class, 'createStep3'])->name('step3');
                Route::post('step-3', [IncomingGoodsController::class, 'storeFinal'])->name('store-final');

                // Show & Cancel
                Route::get('cancel', [IncomingGoodsController::class, 'cancel'])->name('cancel');
                Route::delete('{id}', [IncomingGoodsController::class, 'destroy'])->name('destroy');
                Route::get('{id}', [IncomingGoodsController::class, 'show'])->name('show'); // pindahkan ke bawah

                // Export to Excel
            });

         //Grading Goods Routes
        Route::prefix('grading-goods')->name('grading-goods.')->group(function () {
            Route::get('/', [GradingGoodsController::class, 'index'])->name('index');

            // Step 1: pilih receipt_item (berdasarkan grade supplier), input grading_date
            Route::get('step-1', [GradingGoodsController::class, 'createStep1'])->name('step1');
            Route::post('step-1', [GradingGoodsController::class, 'storeStep1'])->name('step1.store');

            // Step 2: lengkapi quantity, grade company, weight_grams, notes
            Route::get('step-2/{id}', [GradingGoodsController::class, 'createStep2'])->name('step2');
            Route::post('step-2/{id}', [GradingGoodsController::class, 'storeStep2'])->name('step2.store');

            // Edit, Update, Delete
            Route::get('edit/{id}', [GradingGoodsController::class, 'edit'])->name('edit');
            Route::put('update/{id}', [GradingGoodsController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [GradingGoodsController::class, 'destroy'])->name('destroy');

            Route::get('export', [GradingGoodsController::class, 'export'])->name('export');
        });

        // Export Data Master to Excel
        Route::get('suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
        Route::get('locations/export', [LocationController::class, 'export'])->name('locations.export');

        // Master Route
        Route::resource('locations', LocationController::class);
        Route::resource('grade-supplier', GradeSupplierController::class);
        Route::resource('grade-company', GradeCompanyController::class);
        Route::resource('suppliers', SupplierController::class);
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
