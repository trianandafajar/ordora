<?php

use App\Http\Controllers\Api\Admin\CategoryApiController;
use App\Http\Controllers\Api\Admin\KasirApiController;
use App\Http\Controllers\Api\Admin\ProductApiController;
use App\Http\Controllers\Api\Admin\TableApiController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordApiController;
use App\Http\Controllers\Api\Kasir\OrderApiController;
use App\Http\Controllers\Api\Table\CheckoutApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordApiController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordApiController::class, 'resetPassword']);

Route::prefix('table/{qr_token}')->group(function () {
    Route::get('menu', [CheckoutApiController::class, 'showMenu']);
    Route::post('place-order', [CheckoutApiController::class, 'placeOrder']);
});

Route::get('order/tracking/{order_token}', [CheckoutApiController::class, 'showTracking']);
Route::get('order/{order_token}/cash-qr', [CheckoutApiController::class, 'cashQr']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('admin')->group(function () {
        Route::apiResource('products', ProductApiController::class);
        Route::apiResource('categories', CategoryApiController::class);
        Route::apiResource('tables', TableApiController::class);
        Route::apiResource('kasirs', KasirApiController::class);
        Route::patch('kasirs/{user}/toggle', [KasirApiController::class, 'toggle']);
    });

    Route::prefix('cashier')->group(function () {
        Route::apiResource('orders', OrderApiController::class)->only(['index', 'show']);
    });
});
