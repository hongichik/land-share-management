<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
        
        Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
        Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    Route::middleware(['admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Land Rental Contracts routes
        Route::resource('land-rental-contracts', \App\Http\Controllers\Admin\LandRentalContractController::class)->names([
            'index' => 'land-rental-contracts.index',
            'create' => 'land-rental-contracts.create',
            'store' => 'land-rental-contracts.store',
            'show' => 'land-rental-contracts.show',
            'edit' => 'land-rental-contracts.edit',
            'update' => 'land-rental-contracts.update',
            'destroy' => 'land-rental-contracts.destroy',
        ]);

        // Land Rental Prices routes
        Route::prefix('land-rental-contracts/{landRentalContract}/prices')->name('land-rental-prices.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LandRentalPriceController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Admin\LandRentalPriceController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\LandRentalPriceController::class, 'store'])->name('store');
            Route::get('{landRentalPrice}/edit', [\App\Http\Controllers\Admin\LandRentalPriceController::class, 'edit'])->name('edit');
            Route::put('{landRentalPrice}', [\App\Http\Controllers\Admin\LandRentalPriceController::class, 'update'])->name('update');
            Route::delete('{landRentalPrice}', [\App\Http\Controllers\Admin\LandRentalPriceController::class, 'destroy'])->name('destroy');
        });

        // Land Rental Payment Histories routes
        Route::prefix('land-rental-contracts/{landRentalContract}/payment-histories')->name('land-rental-payment-histories.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LandRentalPaymentHistoryController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Admin\LandRentalPaymentHistoryController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\LandRentalPaymentHistoryController::class, 'store'])->name('store');
            Route::get('{landRentalPaymentHistory}', [\App\Http\Controllers\Admin\LandRentalPaymentHistoryController::class, 'show'])->name('show');
            Route::get('{landRentalPaymentHistory}/edit', [\App\Http\Controllers\Admin\LandRentalPaymentHistoryController::class, 'edit'])->name('edit');
            Route::put('{landRentalPaymentHistory}', [\App\Http\Controllers\Admin\LandRentalPaymentHistoryController::class, 'update'])->name('update');
            Route::delete('{landRentalPaymentHistory}', [\App\Http\Controllers\Admin\LandRentalPaymentHistoryController::class, 'destroy'])->name('destroy');
        });
    });
});


