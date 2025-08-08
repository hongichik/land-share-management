<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SecuritiesManagementController;
use App\Http\Controllers\Admin\DividendHistoryController;
use App\Http\Controllers\Admin\DividendPaymentController;

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
        // Authentication routes
        Route::match(['get', 'post'], 'logout', [AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('securities')->name('securities.')->group(function () {
            // Securities Management routes
            Route::prefix('management')->name('management.')->group(function () {
                // Add route to view dividend histories for a specific investor
                Route::get('{securitiesManagement}/dividend-histories', [DividendHistoryController::class, 'getInvestorDividendHistories'])
                    ->name('dividend-histories');
                    
                // Get list of investors for Select2 dropdown
                Route::get('get-investors-list', [SecuritiesManagementController::class, 'getInvestorsList'])
                    ->name('get-investors-list');

                // Individual resource routes
                Route::get('summary-stats', [SecuritiesManagementController::class, 'getSummaryStats'])->name('summary-stats');
                Route::get('/', [SecuritiesManagementController::class, 'index'])->name('index');
                Route::get('/create', [SecuritiesManagementController::class, 'create'])->name('create');
                Route::post('/', [SecuritiesManagementController::class, 'store'])->name('store');
                Route::get('/{securitiesManagement}', [SecuritiesManagementController::class, 'show'])->name('show');
                Route::get('/{securitiesManagement}/edit', [SecuritiesManagementController::class, 'edit'])->name('edit');
                Route::put('/{securitiesManagement}', [SecuritiesManagementController::class, 'update'])->name('update');
                Route::delete('/{securitiesManagement}', [SecuritiesManagementController::class, 'destroy'])->name('destroy');
            });


            // Dividend History routes
            Route::prefix('history')->name('history.')->group(function () {
                Route::get('investor-details', [DividendHistoryController::class, 'getInvestorDetails'])->name('investor-details');
                Route::get('check-existing-payments', [DividendHistoryController::class, 'checkExistingPayments'])->name('check-existing-payments');
                Route::get('all-investors', [DividendHistoryController::class, 'getAllInvestors'])->name('all-investors');
            });
            Route::resource('history', DividendHistoryController::class);
        });

        // Land Rental Contracts routes
        Route::get('land-rental-contracts/export', [\App\Http\Controllers\Admin\LandRentalContractController::class, 'export'])->name('land-rental-contracts.export');
        Route::get('land-rental-contracts/export-tax-calculation', [\App\Http\Controllers\Admin\LandRentalContractController::class, 'exportTaxCalculation'])->name('land-rental-contracts.export-tax-calculation');
        Route::get('land-rental-contracts/export-rental-plan', [\App\Http\Controllers\Admin\LandRentalContractController::class, 'exportRentalPlan'])->name('land-rental-contracts.export-rental-plan');
        Route::get('land-rental-contracts/export-tax-plan', [\App\Http\Controllers\Admin\LandRentalContractController::class, 'exportTaxPlan'])->name('land-rental-contracts.export-tax-plan');
        Route::get('land-rental-contracts/export-non-agri-tax', [\App\Http\Controllers\Admin\LandRentalContractController::class, 'exportNonAgriTax'])->name('land-rental-contracts.export-non-agri-tax');
        Route::resource('land-rental-contracts', \App\Http\Controllers\Admin\LandRentalContractController::class);


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
