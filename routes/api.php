<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\WebhookController;
use App\Http\Controllers\API\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\API\ValidationController;
use App\Http\Controllers\DigiflazzWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Public API Routes (User)
|--------------------------------------------------------------------------
*/

// Order Management (Requires Authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']); // Create order
    Route::get('/orders/{id}', [OrderController::class, 'show']); // Get order status
});

// Webhook Routes (No Authentication Required)
Route::post('/payments/webhook', [WebhookController::class, 'handlePaymentWebhook']); // Payment gateway webhooks

/*
|--------------------------------------------------------------------------
| Admin API Routes (Filament Protected)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Order Management
    Route::get('/orders', [AdminOrderController::class, 'index']); // List orders with filters
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']); // Get order details
    Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']); // Update order status
    
    // Refund Management
    Route::post('/refunds', [AdminOrderController::class, 'processRefund']); // Process manual refund
    
    // Reports
    Route::get('/reports/daily', [AdminOrderController::class, 'dailyReport']); // Daily transaction report
});

/*
|--------------------------------------------------------------------------
| Webhook Routes (External Services)
|--------------------------------------------------------------------------
*/

// Digiflazz Webhook
Route::post('/webhooks/digiflazz', [DigiflazzWebhookController::class, 'handle']);

// Payment Gateway Webhooks (Alternative routes)
Route::post('/webhooks/midtrans', [WebhookController::class, 'handlePaymentWebhook']);
Route::post('/webhooks/xendit', [WebhookController::class, 'handlePaymentWebhook']);
Route::post('/webhooks/tripay', [WebhookController::class, 'handlePaymentWebhook']);

/*
|--------------------------------------------------------------------------
| Legacy API Routes (v1)
|--------------------------------------------------------------------------
*/

// Public API Routes
Route::prefix('v1')->group(function () {
    // Games
    Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/{slug}', [GameController::class, 'show']);
    Route::get('/games/{slug}/denominations', [GameController::class, 'denominations']);
    
    // Check order status
    Route::post('/orders/check', [\App\Http\Controllers\Api\OrderController::class, 'check']);
    
    // Validate promo
    Route::post('/promos/validate', [\App\Http\Controllers\Api\OrderController::class, 'validatePromo']);
    
    // Player ID validation
    Route::post('/validate/player-id', [ValidationController::class, 'validatePlayerId']);
    Route::get('/validate/status/{sessionId}', [ValidationController::class, 'checkValidationStatus']);
});

// Protected API Routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // User orders
    Route::get('/orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::get('/orders/{invoice_no}', [\App\Http\Controllers\Api\OrderController::class, 'show']);
    Route::post('/orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);
    
    // Payment status
    Route::get('/payments/{reference}/status', [PaymentController::class, 'status']);
});