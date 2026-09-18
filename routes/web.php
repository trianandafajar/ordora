<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\KasirController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->role->value === 'admin' ? '/admin/dashboard' : '/cashier/dashboard');
    }

    return redirect()->route('login');
});

// Customer (no auth, prefixed /table)
Route::prefix('table')->name('table.')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'showCheckout'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'storeCheckout'])->name('checkout.store');
    Route::get('/tracking/{order_token}', [CheckoutController::class, 'showTracking'])->name('tracking.show');
    Route::post('/order/{order_token}/confirm', [CheckoutController::class, 'confirmOrder'])->name('order.confirm');
    Route::get('/order/{order_token}', [CheckoutController::class, 'showOrderDetail'])->name('order.detail');
    Route::get('/order/{order_token}/download', [CheckoutController::class, 'downloadReceipt'])->name('order.download');
    Route::get('/order/{order_token}/qris-qr', [CheckoutController::class, 'qrisQr'])->name('order.qrisQr');
    Route::get('/order/{order_token}/cash-qr', [CheckoutController::class, 'cashQr'])->name('order.cashQr');
    Route::get('/history', [CheckoutController::class, 'history'])->name('history');
    Route::get('/{qr_token}', [CheckoutController::class, 'showMenu'])->name('menu');
    Route::post('/{qr_token}/cart', [CheckoutController::class, 'addToCart'])->name('cart.add');
    Route::delete('/{qr_token}/cart/{product_id}', [CheckoutController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/{qr_token}/place-order', [CheckoutController::class, 'placeOrder'])->name('placeOrder');
});

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('tables', TableController::class);
    Route::get('tables/{table}/qr', [TableController::class, 'qr'])->name('tables.qr');
    Route::patch('tables/{table}/regenerate-qr', [TableController::class, 'regenQr'])->name('tables.regenQr');
    Route::resource('cashiers', KasirController::class);
    Route::patch('cashiers/{user}/toggle', [KasirController::class, 'toggle'])->name('cashiers.toggle');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    Route::get('/reports/pdf', [DashboardController::class, 'pdf'])->name('reports.pdf');
});

// Cashier
Route::middleware(['auth', 'role:kasir'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'kasir'])->name('dashboard');
    Route::get('/order/{order}', [KasirOrderController::class, 'show'])->name('order.show');
    Route::get('/order/{order}/receipt', [KasirOrderController::class, 'receipt'])->name('order.receipt');
    Route::patch('/order/{order}/status', [KasirOrderController::class, 'updateStatus'])->name('order.status');
    Route::post('/order/{order}/pay', [KasirOrderController::class, 'pay'])->name('order.pay');
});
