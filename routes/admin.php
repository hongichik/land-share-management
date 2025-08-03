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
        
        // StorageAI routes
        Route::prefix('config-ai/storage-ai')->name('config-ai.storage-ai.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ConfigAI\StorageAIController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\ConfigAI\StorageAIController::class, 'create'])->name('create');
            Route::get('/upload-file', [\App\Http\Controllers\Admin\ConfigAI\StorageAIController::class, 'uploadFile'])->name('upload-file');
            Route::post('/', [\App\Http\Controllers\Admin\ConfigAI\StorageAIController::class, 'store'])->name('store');
            Route::get('/{storageAI}/edit', [\App\Http\Controllers\Admin\ConfigAI\StorageAIController::class, 'edit'])->name('edit');
            Route::put('/{storageAI}', [\App\Http\Controllers\Admin\ConfigAI\StorageAIController::class, 'update'])->name('update');
            Route::delete('/{storageAI}', [\App\Http\Controllers\Admin\ConfigAI\StorageAIController::class, 'destroy'])->name('destroy');
        });
    });
});


