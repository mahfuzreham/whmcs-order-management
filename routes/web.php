<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/login/2fa', [AuthController::class, 'showTwoFactor'])->name('login.2fa');
Route::post('/login/2fa', [AuthController::class, 'verifyTwoFactor'])->name('login.2fa.verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::match(['get','post'], '/payment/callback/{gateway}', [PaymentController::class, 'callback'])->name('payment.callback');

Route::middleware('admin.auth')->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/payments', [AdminController::class, 'payments'])->middleware('admin.permission:payments.view')->name('admin.payments');
    Route::get('/admin/staff', [AdminController::class, 'staff'])->middleware('admin.permission:staff.view')->name('admin.staff');
    Route::post('/admin/staff', [AdminController::class, 'createStaff'])->middleware('admin.permission:staff.manage')->name('admin.staff.create');
    Route::post('/admin/staff/{id}/toggle', [AdminController::class, 'toggleStaff'])->middleware('admin.permission:staff.manage')->name('admin.staff.toggle');
    Route::delete('/admin/staff/{id}', [AdminController::class, 'deleteStaff'])->middleware('admin.permission:staff.manage')->name('admin.staff.delete');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->middleware('admin.permission:settings.manage')->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'saveSettings'])->middleware('admin.permission:settings.manage')->name('admin.settings.save');
});

Route::middleware('portal.auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/services', [PortalController::class, 'services'])->name('services');
    Route::get('/services/{id}', [PortalController::class, 'service'])->name('service');
    Route::get('/domains', [PortalController::class, 'domains'])->name('domains');
    Route::get('/invoices', [PortalController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{id}', [PortalController::class, 'invoice'])->name('invoice');
    Route::get('/pay/invoice/{id}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/pay/invoice/{id}', [PaymentController::class, 'start'])->name('payment.start');
    Route::post('/payment/{id}/refund', [PaymentController::class, 'refund'])->name('payment.refund');
    Route::get('/transactions', [PortalController::class, 'transactions'])->name('transactions');
    Route::get('/tickets', [PortalController::class, 'tickets'])->name('tickets');
    Route::post('/tickets', [PortalController::class, 'createTicket'])->name('tickets.create');
    Route::get('/tickets/{id}', [PortalController::class, 'ticket'])->name('ticket');
    Route::post('/tickets/{id}/reply', [PortalController::class, 'replyTicket'])->name('ticket.reply');
});
