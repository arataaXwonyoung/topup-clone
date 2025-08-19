@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Section -->
    <div class="glass rounded-xl p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-yellow-400 mb-2">
                    Welcome back, {{ $user->name }}!
                </h1>
                <p class="text-gray-400">Level {{ ucfirst($user->level) }} • {{ $user->points }} Points</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-400">Saldo E-Wallet</p>
                <p class="text-2xl font-bold text-yellow-400">Rp {{ number_format($user->balance, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
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
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_spent'] / 1000) }}K</p>
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
                    <p class="text-gray-400 text-sm">Pending</p>
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
                    <p class="text-gray-400 text-sm">Points</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['points'] }}</p>
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
                    <h2 class="text-xl font-semibold text-yellow-400">Riwayat Order Terakhir</h2>
                    <a href="{{ route('user.orders') }}" class="text-sm text-gray-400 hover:text-yellow-400">
                        Lihat Semua →
                    </a>
                </div>
                
                @if($recentOrders->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentOrders as $order)
                        <div class="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800/70 transition">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gray-700 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">{{ $order->game->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-400">{{ $order->denomination->name ?? '-' }}</p>
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
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-200">Belum ada order</h3>
                        <p class="mt-1 text-sm text-gray-400">Mulai top up game favoritmu!</p>
                        <div class="mt-6">
                            <a href="{{ route('home') }}" class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                                Browse Games
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="space-y-6">
            <!-- Active Promos -->
            <div class="glass rounded-xl p-6">
                <h3 class="text-lg font-semibold text-yellow-400 mb-4">Promo Aktif</h3>
                @if($activePromos->count() > 0)
                    <div class="space-y-3">
                        @foreach($activePromos as $promo)
                        <div class="p-3 bg-gray-800/50 rounded-lg">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-mono text-sm font-bold text-yellow-400">{{ $promo->code }}</span>
                                <span class="text-xs text-gray-400">
                                    {{ $promo->type == 'percent' ? $promo->value . '%' : 'Rp ' . number_format($promo->value) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400">
                                Min. Rp {{ number_format($promo->min_total) }}
                                @if($promo->ends_at)
                                    • Exp: {{ $promo->ends_at->format('d M') }}
                                @endif
                            </p>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('user.promos') }}" class="block mt-4 text-center text-sm text-yellow-400 hover:text-yellow-300">
                        Lihat Semua Promo →
                    </a>
                @else
                    <p class="text-sm text-gray-400">Tidak ada promo aktif saat ini</p>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="glass rounded-xl p-6">
                <h3 class="text-lg font-semibold text-yellow-400 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('home') }}" class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg hover:bg-gray-800/70 transition">
                        <span class="text-sm">Top Up Game</span>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    <a href="{{ route('user.profile.edit') }}" class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg hover:bg-gray-800/70 transition">
                        <span class="text-sm">Edit Profile</span>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    <a href="{{ route('user.support') }}" class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg hover:bg-gray-800/70 transition">
                        <span class="text-sm">Support Ticket</span>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection