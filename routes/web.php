<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NetworkDeviceController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

// --- Guest / Authentication Routes ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Demo login switch helper (for quick evaluation)
Route::get('/demo-login/{type}', [AuthController::class, 'demoLogin'])->name('demo.login');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Email verification
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
});

// --- Customer-Facing Captive Portal (Public access for clients connecting to Wi-Fi) ---
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('index');
    Route::get('/{slug}', [PortalController::class, 'show'])->name('show');
    Route::post('/connect', [PortalController::class, 'connect'])->name('connect');
    Route::post('/{slug}/connect', [PortalController::class, 'connect'])->name('connect.slug');
});

// --- Authenticated & Verified Wi-Fi Management Workspace ---
Route::middleware(['auth', 'verified_hotspot'])->group(function () {
    // Dashboard & Overview
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Voucher Management & Sales
    Route::prefix('vouchers')->name('vouchers.')->group(function () {
        Route::get('/', [VoucherController::class, 'index'])->name('index');
        Route::post('/sell', [VoucherController::class, 'store'])->name('sell');
        Route::post('/batch', [VoucherController::class, 'generateBatch'])->name('batch');
        Route::post('/{id}/revoke', [VoucherController::class, 'revoke'])->name('revoke');
        Route::get('/{id}/print', [VoucherController::class, 'printTicket'])->name('print');
    });

    // Wi-Fi Packages
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [PackageController::class, 'index'])->name('index');
        Route::post('/', [PackageController::class, 'store'])->name('store');
        Route::post('/{id}', [PackageController::class, 'update'])->name('update');
    });

    // Active Client Sessions
    Route::prefix('sessions')->name('sessions.')->group(function () {
        Route::get('/', [SessionController::class, 'index'])->name('index');
        Route::post('/{id}/disconnect', [SessionController::class, 'disconnect'])->name('disconnect');
        Route::post('/sync-expired', [SessionController::class, 'syncExpired'])->name('sync');
    });

    // Network Devices & Diagnostics
    Route::prefix('devices')->name('devices.')->group(function () {
        Route::get('/', [NetworkDeviceController::class, 'index'])->name('index');
        Route::post('/', [NetworkDeviceController::class, 'store'])->name('store');
        Route::post('/{id}/ping', [NetworkDeviceController::class, 'pingTest'])->name('ping');
        Route::delete('/{id}', [NetworkDeviceController::class, 'destroy'])->name('destroy');
    });

    // Reports & Analytics
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

    // Staff & Permissions
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::post('/', [StaffController::class, 'store'])->name('store');
        Route::delete('/{id}', [StaffController::class, 'destroy'])->name('destroy');
    });

    // Hotspot & Captive Portal Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
    });

    // Transactions / CSV Export
    Route::get('/export/vouchers', [ExportController::class, 'vouchers'])->name('export.vouchers');
});
