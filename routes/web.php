<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/topup', [HomeController::class, 'index'])->name('topup');

// Game Routes
Route::get('/g/{slug}', [GameController::class, 'show'])->name('games.show');
Route::get('/games', [HomeController::class, 'index'])->name('games.index');

// Feature Pages
Route::get('/leaderboard', [HomeController::class, 'leaderboard'])->name('leaderboard');
Route::get('/cek-transaksi', [HomeController::class, 'checkTransaction'])->name('transactions.check');
Route::get('/kalkulator', [CalculatorController::class, 'index'])->name('calculator');

// Article Routes
Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::post('/artikel/{slug}/increment-view', [ArticleController::class, 'incrementView'])->name('articles.increment-view');

// Checkout Routes
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::post('/checkout/validate-promo', [CheckoutController::class, 'validatePromo'])->name('checkout.validate-promo');

// Invoice Routes  
Route::get('/invoice/{invoice_no}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::get('/invoice/{invoice_no}/download', [InvoiceController::class, 'download'])->name('invoices.download');

// Order routes
Route::post('/orders/{invoice_no}/review', [App\Http\Controllers\OrderController::class, 'review'])->name('orders.review');
Route::get('/orders/{invoice_no}/reorder', [App\Http\Controllers\OrderController::class, 'reorder'])->name('orders.reorder');
Route::delete('/orders/{invoice_no}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');

// Webhook Routes (no CSRF protection)
Route::post('/webhooks/{provider}', [WebhookController::class, 'handle'])
    ->name('webhooks.handle')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Authenticated User Routes
Route::middleware('auth')->group(function () {
    // Dashboard - redirect based on user type
    Route::get('/dashboard', function () {
        if (auth()->user()->is_admin) {
            return redirect('/admin');
        }
        return redirect()->route('user.dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Orders
    Route::get('/orders', [HomeController::class, 'orderHistory'])->name('orders.history');
});

// Admin Routes - Redirect to Filament
Route::middleware(['auth'])->group(function () {
    Route::get('/admin-dashboard', function () {
        if (auth()->user()->is_admin) {
            return redirect('/admin');
        }
        return redirect('/dashboard');
    })->name('admin.dashboard');
});

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\User\DashboardController::class, 'index'])
        ->name('dashboard');

    // Orders
    Route::get('/orders', [App\Http\Controllers\User\OrderController::class, 'index'])
        ->name('orders');
    Route::get('/orders/{invoice}', [App\Http\Controllers\User\OrderController::class, 'show'])
        ->name('orders.show');
    Route::get('/orders/{invoice}/invoice', [App\Http\Controllers\User\OrderController::class, 'downloadInvoice'])
        ->name('orders.invoice');

    // Profile
    Route::get('/profile', [App\Http\Controllers\User\ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\User\ProfileController::class, 'update'])
        ->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\User\ProfileController::class, 'updatePassword'])
        ->name('profile.password');
    Route::put('/profile/notifications', [App\Http\Controllers\User\ProfileController::class, 'updateNotifications'])
        ->name('profile.notifications');

    // Promos
    Route::get('/promos', [App\Http\Controllers\User\PromoController::class, 'index'])
        ->name('promos');

    // Reviews
    Route::get('/reviews', [App\Http\Controllers\User\ReviewController::class, 'index'])
        ->name('reviews');
    Route::post('/reviews/{order}', [App\Http\Controllers\User\ReviewController::class, 'store'])
        ->name('reviews.store');
    Route::put('/reviews/{review}', [App\Http\Controllers\User\ReviewController::class, 'update'])
        ->name('reviews.update');
    Route::delete('/reviews/{review}', [App\Http\Controllers\User\ReviewController::class, 'destroy'])
        ->name('reviews.destroy');

    // Support Tickets
    Route::get('/support', [App\Http\Controllers\User\SupportController::class, 'index'])
        ->name('support');
    Route::get('/support/create', [App\Http\Controllers\User\SupportController::class, 'create'])
        ->name('support.create');
    Route::post('/support', [App\Http\Controllers\User\SupportController::class, 'store'])
        ->name('support.store');
    Route::get('/support/{ticket}', [App\Http\Controllers\User\SupportController::class, 'show'])
        ->name('support.show');
});

// Include auth routes from Breeze
require __DIR__.'/auth.php';