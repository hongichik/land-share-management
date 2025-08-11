<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Thêm route cho việc xuất kế hoạch thuê đất
Route::get('/admin/land-rental-contracts/export-plan', [App\Http\Controllers\Admin\LandRentalContractController::class, 'exportRentalPlan'])
    ->name('admin.land-rental-contracts.export-plan');

    
// Catch-all route: redirect all other URLs to admin.dashboard

