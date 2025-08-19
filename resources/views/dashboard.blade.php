@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Section -->
    <div class="glass rounded-xl p-6 mb-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-2">
            Welcome back, {{ auth()->user()->name }}!
        </h1>
        <p class="text-gray-400">Manage your account and view your transaction history.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Orders</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_orders'] }}</p>
                </div>
                <div class="text-yellow-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Spent</p>
                    <p class="text-2xl font-bold text-white">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</p>
                </div>
                <div class="text-green-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Pending Orders</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['pending_orders'] }}</p>
                </div>
                <div class="text-orange-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="glass rounded-xl p-6 mb-8">
        <h2 class="text-xl font-semibold text-yellow-400 mb-4">Recent Orders</h2>
        
        @if($recentOrders->count() > 0)
            <div class="space-y-4">
                @foreach($recentOrders as $order)
                <div class="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg">
                    <div>
                        <p class="font-semibold">{{ $order->game->name ?? 'Unknown Game' }}</p>
                        <p class="text-sm text-gray-400">{{ $order->denomination->name ?? 'Unknown Item' }}</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-yellow-400">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                        <span class="px-2 py-1 text-xs rounded
                            {{ $order->status == 'DELIVERED' ? 'bg-green-500' : '' }}
                            {{ $order->status == 'PAID' ? 'bg-blue-500' : '' }}
                            {{ in_array($order->status, ['PENDING', 'UNPAID']) ? 'bg-yellow-500' : '' }}
                            {{ in_array($order->status, ['EXPIRED', 'FAILED']) ? 'bg-red-500' : '' }}
                            text-white">
                            {{ $order->status }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="mt-4">
                <a href="{{ route('orders.history') }}" class="text-yellow-400 hover:text-yellow-300">
                    View all orders →
                </a>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-200">No orders yet</h3>
                <p class="mt-1 text-sm text-gray-400">Start by topping up your favorite game!</p>
                <div class="mt-6">
                    <a href="{{ route('home') }}" class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                        Browse Games
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="glass rounded-xl p-6">
        <h2 class="text-xl font-semibold text-yellow-400 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('home') }}" class="p-4 bg-gray-800/50 rounded-lg text-center hover:bg-gray-700/50 transition">
                <svg class="w-8 h-8 mx-auto mb-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="text-sm">Top Up</span>
            </a>
            
            <a href="{{ route('transactions.check') }}" class="p-4 bg-gray-800/50 rounded-lg text-center hover:bg-gray-700/50 transition">
                <svg class="w-8 h-8 mx-auto mb-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span class="text-sm">Check Status</span>
            </a>
            
            <a href="{{ route('profile.edit') }}" class="p-4 bg-gray-800/50 rounded-lg text-center hover:bg-gray-700/50 transition">
                <svg class="w-8 h-8 mx-auto mb-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-sm">Profile</span>
            </a>
            
            <a href="{{ route('calculator') }}" class="p-4 bg-gray-800/50 rounded-lg text-center hover:bg-gray-700/50 transition">
                <svg class="w-8 h-8 mx-auto mb-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm">Calculator</span>
            </a>
        </div>
    </div>
</div>
@endsection