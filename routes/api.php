<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;

// API Routes (Optional - for future mobile app or external integration)

// Public API Routes
Route::prefix('v1')->group(function () {
    // Games
    Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/{slug}', [GameController::class, 'show']);
    Route::get('/games/{slug}/denominations', [GameController::class, 'denominations']);
    
    // Check order status
    Route::post('/orders/check', [OrderController::class, 'check']);
    
    // Validate promo
    Route::post('/promos/validate', [OrderController::class, 'validatePromo']);
});

// Protected API Routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // User orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{invoice_no}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    
    // Payment status
    Route::get('/payments/{reference}/status', [PaymentController::class, 'status']);
});