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
    });
});


