<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\ProductApiController;
use App\Http\Controllers\Api\Admin\CategoryApiController;
use App\Http\Controllers\Api\Admin\TableApiController;
use App\Http\Controllers\Api\Admin\KasirApiController;
use App\Http\Controllers\Api\Kasir\OrderApiController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordApiController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordApiController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordApiController::class, 'resetPassword']);

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
