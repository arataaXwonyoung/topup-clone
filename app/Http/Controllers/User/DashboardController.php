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
        
        // Ensure user has default values
        if (is_null($user->balance)) {
            $user->balance = 0;
        }
        if (is_null($user->points)) {
            $user->points = 0;
        }
        if (is_null($user->level)) {
            $user->level = 'bronze';
        }
        $user->save();
        
        // User statistics
        $stats = [
            'balance' => $user->balance ?? 0,
            'points' => $user->points ?? 0,
            'level' => $user->level ?? 'bronze',
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->sum('total') ?? 0,
            'pending_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', ['PENDING', 'UNPAID'])
                ->count(),
        ];
        
        // Recent orders with error handling
        try {
            $recentOrders = Order::where('user_id', $user->id)
                ->with(['game', 'denomination'])
                ->latest()
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $recentOrders = collect();
            \Log::warning('Error loading recent orders for user ' . $user->id . ': ' . $e->getMessage());
        }
        
        // Active promos with error handling
        try {
            $activePromos = Promo::active()
                ->limit(3)
                ->get();
        } catch (\Exception $e) {
            $activePromos = collect();
            \Log::warning('Error loading active promos: ' . $e->getMessage());
        }
        
        return view('user.dashboard', compact('user', 'stats', 'recentOrders', 'activePromos'));
    }
}