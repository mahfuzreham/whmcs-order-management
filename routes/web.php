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
    Route::get('/services/{id}', [PortalController::class, 'service'])->name('service');
    Route::get('/domains', [PortalController::class, 'domains'])->name('domains');
    Route::get('/invoices', [PortalController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{id}', [PortalController::class, 'invoice'])->name('invoice');
    Route::get('/transactions', [PortalController::class, 'transactions'])->name('transactions');
    Route::get('/tickets', [PortalController::class, 'tickets'])->name('tickets');
    Route::post('/tickets', [PortalController::class, 'createTicket'])->name('tickets.create');
    Route::get('/tickets/{id}', [PortalController::class, 'ticket'])->name('ticket');
    Route::post('/tickets/{id}/reply', [PortalController::class, 'replyTicket'])->name('ticket.reply');
});
