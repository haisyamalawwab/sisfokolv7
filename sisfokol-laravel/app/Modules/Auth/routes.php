<?php

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Controllers\PasswordResetController;
use App\Modules\Auth\Controllers\ImpersonationController;
use App\Modules\Auth\Controllers\DashboardController;
use App\Modules\Auth\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/password/change', [PasswordResetController::class, 'show'])->name('password.change');
        Route::post('/password/change', [PasswordResetController::class, 'store'])->name('password.change.store');

        Route::post('/impersonate/{target}/start', [ImpersonationController::class, 'start'])->name('impersonate.start');
        Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    });
});
