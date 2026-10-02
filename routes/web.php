<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UnifiedDashboardController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\UpdateController;

Route::get('/install', [InstallController::class, 'index'])->name('install');
Route::post('/install', [InstallController::class, 'configure'])->name('install.configure');
Route::get('/install/done', [InstallController::class, 'done'])->name('install.done');

Route::get('/', fn () => redirect()->route('dashboard'));
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove/{key}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/dashboard', [UnifiedDashboardController::class, 'show'])->name('dashboard');
Route::post('/dashboard', [UnifiedDashboardController::class, 'login'])->name('dashboard.login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/login/2fa', [AuthController::class, 'showTwoFactor'])->name('login.2fa');
Route::post('/login/2fa', [AuthController::class, 'verifyTwoFactor'])->name('login.2fa.verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// bKash returns the browser to this endpoint with paymentID; execution is verified server-side.
Route::get('/payment/callback/{gateway}', [PaymentController::class, 'callback'])->name('payment.callback');

Route::middleware('admin.auth')->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/payments', [AdminController::class, 'payments'])->middleware('admin.permission:payments.view')->name('admin.payments');
    Route::get('/admin/staff', [AdminController::class, 'staff'])->middleware('admin.permission:staff.view')->name('admin.staff');
    Route::post('/admin/staff', [AdminController::class, 'createStaff'])->middleware('admin.permission:staff.manage')->name('admin.staff.create');
    Route::post('/admin/staff/{id}/toggle', [AdminController::class, 'toggleStaff'])->middleware('admin.permission:staff.manage')->name('admin.staff.toggle');
    Route::delete('/admin/staff/{id}', [AdminController::class, 'deleteStaff'])->middleware('admin.permission:staff.delete')->name('admin.staff.delete');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->middleware('admin.permission:settings.manage')->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'saveSettings'])->middleware('admin.permission:settings.manage')->name('admin.settings.save');
    Route::get('/admin/updates', [UpdateController::class, 'index'])->middleware('admin.permission:settings.manage')->name('admin.updates');
    Route::post('/admin/updates/check', [UpdateController::class, 'check'])->middleware('admin.permission:settings.manage')->name('admin.updates.check');
    Route::post('/admin/updates/install', [UpdateController::class, 'install'])->middleware('admin.permission:settings.manage')->name('admin.updates.install');
    Route::post('/admin/updates/settings', [UpdateController::class, 'saveSettings'])->middleware('admin.permission:settings.manage')->name('admin.updates.settings');
});

Route::middleware('portal.auth')->group(function () {
    Route::get('/dashboard/home', DashboardController::class)->name('dashboard.home');
    Route::get('/dashboard/hosting', [HostingController::class, 'index'])->name('hosting');
    Route::get('/dashboard/hosting/{id}', [HostingController::class, 'service'])->name('hosting.service');
    Route::get('/dashboard/hosting/{id}/cpanel', [HostingController::class, 'cpanel'])->name('hosting.cpanel');

    Route::prefix('dashboard/reseller')->group(function () {
        Route::get('/', [ResellerController::class, 'index'])->name('reseller');
        Route::get('/accounts', [ResellerController::class, 'accounts'])->name('reseller.accounts');
        Route::post('/accounts', [ResellerController::class, 'createAccount'])->name('reseller.accounts.create');
        Route::post('/accounts/{user}/suspend', [ResellerController::class, 'suspend'])->name('reseller.accounts.suspend');
        Route::post('/accounts/{user}/unsuspend', [ResellerController::class, 'unsuspend'])->name('reseller.accounts.unsuspend');
        Route::post('/accounts/{user}/terminate', [ResellerController::class, 'terminate'])->name('reseller.accounts.terminate');
        Route::post('/accounts/{user}/package', [ResellerController::class, 'changePackage'])->name('reseller.accounts.package');
        Route::get('/packages', [ResellerController::class, 'packages'])->name('reseller.packages');
        Route::post('/packages', [ResellerController::class, 'createPackage'])->name('reseller.packages.create');
        Route::post('/packages/{pkg}/delete', [ResellerController::class, 'deletePackage'])->name('reseller.packages.delete');
    });

    Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/cart/checkout', [CartController::class, 'placeOrder'])->name('cart.checkout.place');
    Route::prefix('dashboard')->group(function () {
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
        Route::get('/orders', [PortalController::class, 'orders'])->name('orders');
    });
});
