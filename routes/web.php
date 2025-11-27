<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Master\GradeCompanyController;
use App\Http\Controllers\Feature\GradingGoodsController;
use App\Http\Controllers\Master\GradeSupplierController;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Location;
use App\Exports\GradeSupplierExport;
use App\Http\Controllers\Feature\BarangKeluarController;
use App\Http\Controllers\Feature\PenjualanController;
use App\Http\Controllers\Feature\TransferInternalController;
use App\Http\Controllers\Feature\TransferExternalController;
use App\Http\Controllers\Master\StokController;
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

                // Show & Cancel & Delete & Edit
                Route::get('cancel', [IncomingGoodsController::class, 'cancel'])->name('cancel');
                Route::get('{id}/edit', [IncomingGoodsController::class, 'edit'])->name('edit');
                Route::put('{id}', [IncomingGoodsController::class, 'update'])->name('update');
                Route::delete('{id}', [IncomingGoodsController::class, 'destroy'])->name('destroy');
                Route::get('{id}', [IncomingGoodsController::class, 'show'])->name('show');
            });

        //Grading Goods Routes
        Route::prefix('grading-goods')
            ->name('grading-goods.')
            ->group(function () {
                Route::get('/', [GradingGoodsController::class, 'index'])->name('index');

                Route::get('export', [GradingGoodsController::class, 'export'])->name('export');

                // Step 1
                Route::get('step-1', [GradingGoodsController::class, 'createStep1'])->name('step1');
                Route::post('step-1', [GradingGoodsController::class, 'storeStep1'])->name('step1.store');

                // Step 2
                Route::get('step-2/{id}', [GradingGoodsController::class, 'createStep2'])->name('step2');
                Route::post('step-2/{id}', [GradingGoodsController::class, 'storeStep2'])->name('step2.store');

                // Edit, Update, Delete
                Route::get('edit/{id}', [GradingGoodsController::class, 'edit'])->name('edit');
                Route::put('update/{id}', [GradingGoodsController::class, 'update'])->name('update');
                Route::delete('delete/{id}', [GradingGoodsController::class, 'destroy'])->name('destroy');

                Route::get('/{id}', [GradingGoodsController::class, 'show'])->name('show');
            });

        // Export Data Master to Excel
        Route::get('suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
        Route::get('locations/export', [LocationController::class, 'export'])->name('locations.export');
        Route::get('/grade-supplier/export', function () {
            return Excel::download(new GradeSupplierExport(), 'grade_suppliers.xlsx');
        })->name('grade-supplier.export');

        Route::prefix('barang-keluar')
            ->name('barang.keluar.')
            ->group(function () {
                // ========== INDEX ==========
                Route::get('/', [BarangKeluarController::class, 'index'])->name('index');

                // ========== PENJUALAN ==========
                Route::get('sell', [PenjualanController::class, 'sellForm'])->name('sell.form');
                Route::post('sell', [PenjualanController::class, 'sell'])->name('sell.store');
                Route::get('sell/stock-check', [PenjualanController::class, 'checkStock'])->name('sell.stock_check');

                // History actions
                Route::get('sell/{id}/edit', [PenjualanController::class, 'edit'])->name('sell.edit');
                Route::put('sell/{id}', [PenjualanController::class, 'update'])->name('sell.update');
                Route::delete('sell/{id}', [PenjualanController::class, 'destroy'])->name('sell.destroy');

                // ========== TRANSFER INTERNAL ==========
                Route::prefix('transfer')
                    ->name('transfer.')
                    ->group(function () {
                        Route::get('/step1', [TransferInternalController::class, 'transferStep1'])->name('step1');
                        Route::post('/step1', [TransferInternalController::class, 'storeTransferStep1'])->name('store-step1');
                        Route::get('/step2', [TransferInternalController::class, 'transferStep2'])->name('step2');
                        Route::post('/confirm', [TransferInternalController::class, 'transfer'])->name('store');
                        Route::get('/stock-check', [TransferInternalController::class, 'checkStock'])->name('stock_check');
                    });

                // ========== TRANSFER EXTERNAL ==========
                Route::prefix('transfer-external')
                    ->name('external-transfer.')
                    ->group(function () {
                        Route::get('/step1', [TransferExternalController::class, 'externalTransferStep1'])->name('step1');
                        Route::post('/step1', [TransferExternalController::class, 'storeExternalTransferStep1'])->name('store-step1');
                        Route::get('/step2', [TransferExternalController::class, 'externalTransferStep2'])->name('step2');
                        Route::post('/confirm', [TransferExternalController::class, 'externalTransfer'])->name('store');
                    });
            });

        // Di dalam group middleware 'auth' and 'prefix' admin Anda
        Route::get('/tracking-stok', [StokController::class, 'index'])->name('stok.tracking.index');

        // Master Route
        Route::resource('locations', LocationController::class);
        Route::resource('grade-supplier', GradeSupplierController::class);
        Route::resource('grade-company', GradeCompanyController::class);
        Route::resource('suppliers', SupplierController::class);
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
