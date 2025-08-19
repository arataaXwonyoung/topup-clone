<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Order;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define gates
        Gate::define('view-order', function (User $user, Order $order) {
            return $user->id === $order->user_id || $user->isAdmin();
        });

        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-orders', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-games', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-reports', function (User $user) {
            return $user->isAdmin();
        });
    }
}