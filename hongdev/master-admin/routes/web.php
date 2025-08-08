<?php

use Illuminate\Support\Facades\Route;
use Hongdev\MasterAdmin\Http\Controllers\MasterAdminController;
use Hongdev\MasterAdmin\Http\Controllers\LogController;
use Hongdev\MasterAdmin\Http\Controllers\SettingsController;
use Hongdev\MasterAdmin\Http\Controllers\Settings\DatabaseController;
use Hongdev\MasterAdmin\Http\Controllers\Settings\EnvironmentController;
use Hongdev\MasterAdmin\Http\Controllers\Settings\MailController;
use Hongdev\MasterAdmin\Http\Controllers\Settings\DriveController;
use Hongdev\MasterAdmin\Http\Controllers\BackupController;
use Hongdev\MasterAdmin\Http\Controllers\CodeGenerator\AuthGeneratorController;
use Hongdev\MasterAdmin\Http\Controllers\FileManagerController;
use Hongdev\MasterAdmin\Http\Controllers\Settings\DatabaseManagerController;

Route::middleware('master-admin')->prefix('master-admin')->name('master-admin.')->group(function () {
    // Dashboard
    Route::get('/', [MasterAdminController::class, 'index'])->name('dashboard');

    // Artisan Commands
    Route::get('/commands/{command}', [MasterAdminController::class, 'executeCommand'])->name('commands.execute');

    // System metrics
    Route::get('system/metrics', [MasterAdminController::class, 'getPerformanceMetrics']);

    // Logs
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/view', [LogController::class, 'viewLogs'])->name('view');
        Route::get('/clear', [LogController::class, 'clearLogs'])->name('clear');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        // Main settings page
        Route::get('/', [SettingsController::class, 'index'])->name('index');

        // Environment settings
        Route::prefix('environment')->name('environment.')->group(function () {
            Route::get('/', [EnvironmentController::class, 'index'])->name('index');
            Route::get('/change/{env}', [EnvironmentController::class, 'change'])->name('change');
            Route::get('/debug/{mode}', [EnvironmentController::class, 'debug'])->name('debug');
        });

        // Database settings
        Route::prefix('database')->name('database.')->group(function () {
            Route::get('/', [DatabaseController::class, 'index'])->name('index');
            Route::post('/', [DatabaseController::class, 'update'])->name('update');
            Route::get('/test', [DatabaseController::class, 'test'])->name('test');
            Route::delete('/drop-all', [DatabaseController::class, 'dropAllTables'])->name('drop-all');
            Route::get('/import', [DatabaseController::class, 'showImport'])->name('import');
            Route::post('/import', [DatabaseController::class, 'importSql'])->name('import.post');
            Route::post('/execute', [DatabaseController::class, 'executeQuery'])->name('execute');

            // Database Manager routes
            Route::prefix('manager')->name('manager.')->group(function () {
                Route::get('/', [DatabaseManagerController::class, 'index'])->name('index');
                Route::get('/create-table', [DatabaseManagerController::class, 'createTable'])->name('create-table');
                Route::post('/create-table', [DatabaseManagerController::class, 'storeTable'])->name('store-table');
                Route::get('/table/{table}', [DatabaseManagerController::class, 'showTable'])->name('table');
                Route::delete('/table/{table}', [DatabaseManagerController::class, 'dropTable'])->name('drop-table');
                Route::get('/table/{table}/export', [DatabaseManagerController::class, 'exportTable'])->name('export-table');
                Route::get('/table/{table}/create', [DatabaseManagerController::class, 'createRecord'])->name('create-record');
                Route::post('/table/{table}/create', [DatabaseManagerController::class, 'storeRecord'])->name('store-record');
                Route::get('/table/{table}/edit/{id}', [DatabaseManagerController::class, 'editRecord'])->name('edit-record');
                Route::put('/table/{table}/edit/{id}', [DatabaseManagerController::class, 'updateRecord'])->name('update-record');
                Route::delete('/table/{table}/delete/{id}', [DatabaseManagerController::class, 'deleteRecord'])->name('delete-record');
            });
        });

        // Mail settings
        Route::prefix('mail')->name('mail.')->group(function () {
            Route::get('/', [MailController::class, 'index'])->name('index');
            Route::post('/', [MailController::class, 'update'])->name('update');
            Route::get('/test', [MailController::class, 'test'])->name('test');
        });

        // Google Drive settings
        Route::prefix('drive')->name('drive.')->group(function () {
            Route::get('/', [DriveController::class, 'index'])->name('index');
            Route::post('/', [DriveController::class, 'update'])->name('update');
            Route::get('/test', [DriveController::class, 'test'])->name('test');
        });
    });

    // File Manager
    Route::prefix('file-manager')->name('file-manager.')->group(function () {
        Route::get('/', [FileManagerController::class, 'index'])->name('index');
        Route::post('/create-directory', [FileManagerController::class, 'createDirectory'])->name('create-directory');
        Route::post('/upload', [FileManagerController::class, 'upload'])->name('upload');
        Route::delete('/delete', [FileManagerController::class, 'delete'])->name('delete');
        Route::get('/download', [FileManagerController::class, 'download'])->name('download');
        Route::get('/view', [FileManagerController::class, 'view'])->name('view');
        Route::match(['GET', 'POST'], '/edit', [FileManagerController::class, 'edit'])->name('edit');
    });

    // Backup
    Route::prefix('backup')->name('backup.')->group(function () {
        // Trang backup, hiển thị lựa chọn backup
        Route::get('/', [BackupController::class, 'index'])->name('index');
        // Thực hiện backup database (chỉ data)
        Route::post('/database', [BackupController::class, 'backupDatabase'])->name('database');
        // Thực hiện backup storage
        Route::post('/storage', [BackupController::class, 'backupStorage'])->name('storage');
        // Thực hiện backup toàn bộ (trừ vendor, node_modules)
        Route::post('/full', [BackupController::class, 'backupFull'])->name('full');
        // Thực hiện backup tất cả (bao gồm cả vendor, node_modules)
        Route::post('/all', [BackupController::class, 'backupAll'])->name('all');
        // Lưu file backup lên Google Drive
        Route::post('/upload', [BackupController::class, 'uploadToDrive'])->name('upload');
    });


    Route::prefix('code-generator')->name('code-generator.')->group(function () {
        Route::get('/auth', [AuthGeneratorController::class, 'index'])->name('auth.index');
        Route::post('/auth/generate', [AuthGeneratorController::class, 'generate'])->name('auth.generate');
        
        // Migration management routes
        Route::prefix('migrations')->name('migrations.')->group(function () {
            Route::get('/', [\Hongdev\MasterAdmin\Http\Controllers\Settings\MigrationController::class, 'index'])->name('index');
            Route::get('/search', [\Hongdev\MasterAdmin\Http\Controllers\Settings\MigrationController::class, 'search'])->name('search');
            Route::get('/details/{filename}', [\Hongdev\MasterAdmin\Http\Controllers\Settings\MigrationController::class, 'details'])->name('details');
        });
    });
});
