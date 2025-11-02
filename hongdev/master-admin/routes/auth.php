<?php

use Illuminate\Support\Facades\Route;
use Hongdev\MasterAdmin\Http\Controllers\ExportTheme\AuthController;

Route::group(['middleware' => 'guest'], function () {
    // Login Routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login']);

    // Registration Routes
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/register', [AuthController::class, 'register']);

    // Password Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('auth.forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');
});

Route::group(['middleware' => 'auth'], function () {
    // Logout Route
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});
