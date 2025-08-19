<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // If admin, redirect to admin panel
        if ($user->is_admin) {
            return redirect('/admin');
        }
        
        // For regular users
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['game', 'denomination'])
            ->latest()
            ->limit(5)
            ->get();
            
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->sum('total'),
            'pending_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', ['PENDING', 'UNPAID'])
                ->count(),
        ];
        
        return view('dashboard', compact('user', 'recentOrders', 'stats'));
    }
}