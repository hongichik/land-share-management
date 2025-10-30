<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Securities\DividendController;
use App\Http\Controllers\Admin\Securities\DividendRecordController;
use App\Http\Controllers\Admin\Securities\SecuritiesManagementController;
use App\Http\Controllers\Admin\LandRental\LandRentalContractController;
use App\Http\Controllers\Admin\LandRental\LandRentalPriceController;
use App\Http\Controllers\Admin\LandRental\LandRentalPaymentHistoryController;

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

        // Settings routes
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::match(['get', 'post'], '/', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('index');
            Route::get('/{setting}/edit', [\App\Http\Controllers\Admin\SettingController::class, 'edit'])->name('edit');
            Route::put('/{setting}', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('update');
        });

        Route::prefix('securities')->name('securities.')->group(function () {
            // Securities Management routes
            Route::prefix('management')->name('management.')->group(function () {
                // Get list of investors for Select2 dropdown
                Route::get('get-investors-list', [SecuritiesManagementController::class, 'getInvestorsList'])
                    ->name('get-investors-list');

                // Import routes
                Route::post('import-preview', [SecuritiesManagementController::class, 'importPreview'])
                    ->name('import-preview');
                Route::post('import-confirm', [SecuritiesManagementController::class, 'importConfirm'])
                    ->name('import-confirm');

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

            Route::prefix('dividend')->name('dividend.')->group(function () {
                Route::get('get-investors-list', [DividendController::class, 'getInvestorsList'])
                    ->name('get-investors-list');
                Route::get('get-banks-list', [DividendController::class, 'getBanksList'])
                    ->name('get-banks-list');

                // Import routes
                Route::post('import-preview', [DividendController::class, 'importPreview'])
                    ->name('import-preview');
                Route::post('import-confirm', [DividendController::class, 'importConfirm'])
                    ->name('import-confirm');

                // Payment routes
                Route::get('payment', [DividendController::class, 'paymentPage'])
                    ->name('payment');
                Route::post('payment/search', [DividendController::class, 'searchInvestors'])
                    ->name('payment.search');
                Route::post('payment/process', [DividendController::class, 'processPayment'])
                    ->name('payment.process');

                // Individual resource routes
                Route::get('summary-stats', [DividendController::class, 'getSummaryStats'])->name('summary-stats');
                Route::get('/', [DividendController::class, 'index'])->name('index');
                Route::get('/{securitiesManagement}/dividend-details', [DividendController::class, 'dividendDetails'])
                    ->name('dividend-details');
                Route::put('/{securitiesManagement}/update-bank', [DividendController::class, 'updateBank'])->name('update-bank');
                Route::delete('/{dividend}', [DividendController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('dividend-record')->name('dividend-record.')->group(function () {
                Route::get('/', [DividendRecordController::class, 'index'])->name('index');
                Route::get('/export', [DividendRecordController::class, 'export'])->name('export');
                Route::get('/paid', [DividendRecordController::class, 'paid'])->name('paid');
                Route::get('/paid/detail/{transferDate}', [DividendRecordController::class, 'paidDetail'])->name('paid.detail');
                Route::get('/unpaid', [DividendRecordController::class, 'unpaid'])->name('unpaid');
                Route::get('/unpaid/detail/{investorId}', [DividendRecordController::class, 'unpaidDetail'])->name('unpaid.detail');
                Route::get('/detail/{paymentDate}', [DividendRecordController::class, 'detail'])->name('detail');
                Route::delete('/{paymentDate}', [DividendRecordController::class, 'destroy'])->name('destroy');
            });
        });

        Route::prefix('land-rental')->name('land-rental.')->group(function () {
            // Land Rental Contracts routes - export routes first
            Route::get('contracts/export', [LandRentalContractController::class, 'export'])->name('contracts.export');
            Route::get('contracts/export-tax-calculation', [LandRentalContractController::class, 'exportTaxCalculation'])->name('contracts.export-tax-calculation');
            Route::get('contracts/export-rental-plan', [LandRentalContractController::class, 'exportRentalPlan'])->name('contracts.export-rental-plan');
            Route::get('contracts/export-tax-plan', [LandRentalContractController::class, 'exportTaxPlan'])->name('contracts.export-tax-plan');
            Route::get('contracts/export-non-agri-tax', [LandRentalContractController::class, 'exportNonAgriTax'])->name('contracts.export-non-agri-tax');
            Route::resource('contracts', LandRentalContractController::class);

            // Land Rental Prices routes
            Route::prefix('contracts/{landRentalContract}/prices')->name('prices.')->group(function () {
                Route::get('/', [LandRentalPriceController::class, 'index'])->name('index');
                Route::get('create', [LandRentalPriceController::class, 'create'])->name('create');
                Route::post('/', [LandRentalPriceController::class, 'store'])->name('store');
                Route::get('{landRentalPrice}/edit', [LandRentalPriceController::class, 'edit'])->name('edit');
                Route::put('{landRentalPrice}', [LandRentalPriceController::class, 'update'])->name('update');
                Route::delete('{landRentalPrice}', [LandRentalPriceController::class, 'destroy'])->name('destroy');
            });

            // Land Rental Payment Histories routes
            Route::prefix('contracts/{landRentalContract}/payment-histories')->name('payment-histories.')->group(function () {
                Route::get('/', [LandRentalPaymentHistoryController::class, 'index'])->name('index');
                Route::get('create', [LandRentalPaymentHistoryController::class, 'create'])->name('create');
                Route::post('/', [LandRentalPaymentHistoryController::class, 'store'])->name('store');
                Route::get('{landRentalPaymentHistory}', [LandRentalPaymentHistoryController::class, 'show'])->name('show');
                Route::get('{landRentalPaymentHistory}/edit', [LandRentalPaymentHistoryController::class, 'edit'])->name('edit');
                Route::put('{landRentalPaymentHistory}', [LandRentalPaymentHistoryController::class, 'update'])->name('update');
                Route::delete('{landRentalPaymentHistory}', [LandRentalPaymentHistoryController::class, 'destroy'])->name('destroy');
            });
        });
    });
});


Route::any('{any}', function () {
    return redirect()->route('admin.dashboard');
})->where('any', '.*');
