<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/login/2fa', [AuthController::class, 'showTwoFactor'])->name('login.2fa');
Route::post('/login/2fa', [AuthController::class, 'verifyTwoFactor'])->name('login.2fa.verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('portal.auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/services', [PortalController::class, 'services'])->name('services');
    Route::get('/domains', [PortalController::class, 'domains'])->name('domains');
    Route::get('/invoices', [PortalController::class, 'invoices'])->name('invoices');
});
