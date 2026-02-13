<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Sale\SaleController;

use App\Http\Middleware\FixedTokenMiddleware;

Route::middleware([FixedTokenMiddleware::class]) 
    ->prefix('products')
    ->group(function () {
        Route::get('/', [ProductController::class, 'getAll']);
        Route::get('/{id}', [ProductController::class, 'getById']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'delete']);
    });

Route::prefix('sales')->group(function () {
    Route::post('/', [SaleController::class, 'store']);
    Route::get('/{id}', [SaleController::class, 'show']);
    Route::post('/pix-webhook', [SaleController::class, 'pixWebhook'])
        ->name('sales.pix-webhook');
});

