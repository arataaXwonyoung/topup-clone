<?php
// app/Http/Controllers/User/DashboardController.php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Promo;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // User statistics
        $stats = [
            'balance' => $user->balance,
            'points' => $user->points,
            'level' => $user->level,
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->sum('total'),
            'pending_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', ['PENDING', 'UNPAID'])
                ->count(),
        ];
        
        // Recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['game', 'denomination'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Active promos
        $activePromos = Promo::active()
            ->where('is_active', true)
            ->limit(3)
            ->get();
        
        return view('user.dashboard', compact('user', 'stats', 'recentOrders', 'activePromos'));
    }
}