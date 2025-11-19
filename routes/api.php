<?php

use App\Http\Controllers\BugReportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('users')->group(function () {
        Route::resource('users', UserController::class);
        Route::prefix('webhooks')->group(function () {
            Route::post('store', [UserController::class, 'storeWebhooks']);
        });
    });
    Route::prefix('products')->group(function () {
        Route::resource('products', ProductController::class);
        Route::prefix('webhook')->group(function () {
            Route::post('update', [ProductController::class, 'updateWebhooks']);
        });
    });
    Route::prefix('orders')->group(function () {
        Route::resource('orders', OrderController::class);
        Route::prefix('webhook')->group(function () {
            Route::post('store', [OrderController::class, 'storeWebhooks']);
        });
    });
    Route::post('reportBug', [BugReportController::class, 'store'])->name('reportBug');
});
