<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use App\Services\Payment\PaymentManager;
use App\Services\Payment\PaymentService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Payment Service
        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager();
        });

        // Register Payment Service Interface
        $this->app->bind(PaymentService::class, function ($app) {
            return $app->make(PaymentManager::class)->driver();
        });

        // Register custom Filament logout response
        $this->app->bind(
            \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class,
            \App\Http\Responses\AdminLogoutResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for MySQL older versions
        Schema::defaultStringLength(191);
        
        // Use Bootstrap for pagination
        Paginator::useBootstrapFive();
        
        // Share global data to all views
        view()->share('appName', config('app.name'));
        
        // Model observers
        $this->registerObservers();
    }

    /**
     * Register model observers.
     */
    protected function registerObservers(): void
    {
        // You can register model observers here
        // Example: Order::observe(OrderObserver::class);
    }
}