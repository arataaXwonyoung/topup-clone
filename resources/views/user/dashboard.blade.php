@extends('layouts.app')

@section('title', 'User Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Section -->
    <div class="glass rounded-xl p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-yellow-400 mb-2">
                    Welcome, {{ $user->name }}!
                </h1>
                <p class="text-gray-400">Level {{ ucfirst($user->level) }} Member</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-400">Balance</div>
                <div class="text-2xl font-bold text-yellow-400">{{ $user->formatted_balance }}</div>
                <div class="text-sm text-gray-400 mt-2">Points</div>
                <div class="text-xl font-semibold">{{ $user->formatted_points }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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

        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Your Points</p>
                    <p class="text-2xl font-bold text-white">{{ $user->points }}</p>
                </div>
                <div class="text-purple-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Orders -->
        <div class="lg:col-span-2">
            <div class="glass rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-yellow-400">Recent Orders</h2>
                    <a href="{{ route('user.orders') }}" class="text-sm text-yellow-400 hover:text-yellow-300">View All →</a>
                </div>
                
                @if($recentOrders->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentOrders as $order)
                        <div class="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gray-700 rounded-lg flex items-center justify-center">
                                    <i data-lucide="gamepad-2" class="w-6 h-6 text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold">{{ $order->game->name ?? 'Unknown Game' }}</p>
                                    <p class="text-sm text-gray-400">{{ $order->denomination->name ?? 'Unknown Item' }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                                </div>
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
                @else
                    <div class="text-center py-8">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-4 text-gray-500"></i>
                        <p class="text-gray-400">No orders yet</p>
                        <a href="{{ route('home') }}" class="mt-4 inline-block px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500">
                            Start Shopping
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Active Promos -->
        <div class="lg:col-span-1">
            <div class="glass rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-yellow-400">Active Promos</h2>
                    <a href="{{ route('user.promos') }}" class="text-sm text-yellow-400 hover:text-yellow-300">View All →</a>
                </div>
                
                @if($activePromos->count() > 0)
                    <div class="space-y-3">
                        @foreach($activePromos as $promo)
                        <div class="p-3 bg-gradient-to-r from-yellow-400/10 to-orange-400/10 rounded-lg border border-yellow-400/20">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-yellow-400">{{ $promo->code }}</span>
                                <span class="text-xs bg-yellow-400 text-gray-900 px-2 py-1 rounded">
                                    {{ $promo->type == 'percent' ? $promo->value . '%' : 'Rp ' . number_format($promo->value, 0, ',', '.') }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400">
                                Min. Rp {{ number_format($promo->min_total, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500">
                                Valid until {{ $promo->ends_at->format('d M Y') }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-center">No active promos</p>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="glass rounded-xl p-6 mt-6">
                <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('home') }}" class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg hover:bg-gray-700/50 transition">
                        <span class="flex items-center">
                            <i data-lucide="shopping-cart" class="w-5 h-5 mr-3 text-yellow-400"></i>
                            Top Up Games
                        </span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('user.reviews') }}" class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg hover:bg-gray-700/50 transition">
                        <span class="flex items-center">
                            <i data-lucide="star" class="w-5 h-5 mr-3 text-yellow-400"></i>
                            Write Review
                        </span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('user.support') }}" class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg hover:bg-gray-700/50 transition">
                        <span class="flex items-center">
                            <i data-lucide="help-circle" class="w-5 h-5 mr-3 text-yellow-400"></i>
                            Get Support
                        </span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize Lucide icons
    lucide.createIcons();
</script>
@endpush
@endsection