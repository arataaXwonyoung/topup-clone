@extends('layouts.app')

@section('title', 'User Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-8">
    <!-- Welcome Section -->
    <div class="glass rounded-xl p-4 sm:p-6 mb-6 sm:mb-8 dashboard-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div class="text-center sm:text-left">
                <h1 class="text-2xl sm:text-3xl font-bold text-yellow-400 mb-2">
                    Welcome, {{ $user->name }}!
                </h1>
                <p class="text-gray-400">Level {{ ucfirst($user->level ?? 'bronze') }} Member</p>
            </div>
            <div class="text-center sm:text-right">
                <div class="text-sm text-gray-400">Balance</div>
                <div class="text-xl sm:text-2xl font-bold text-yellow-400">
                    {{ $user->formatted_balance ?? 'Rp 0' }}
                </div>
                <div class="text-sm text-gray-400 mt-2">Points</div>
                <div class="text-lg sm:text-xl font-semibold">
                    {{ $user->formatted_points ?? '0' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8">
        <div class="glass rounded-xl p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-xs sm:text-sm">Total Orders</p>
                    <p class="text-lg sm:text-2xl font-bold text-white">{{ $stats['total_orders'] ?? 0 }}</p>
                </div>
                <div class="text-yellow-400">
                    <i data-lucide="shopping-bag" class="w-6 h-6 sm:w-10 sm:h-10"></i>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-xs sm:text-sm">Total Spent</p>
                    <p class="text-lg sm:text-2xl font-bold text-white">Rp {{ number_format($stats['total_spent'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="text-green-400">
                    <i data-lucide="dollar-sign" class="w-6 h-6 sm:w-10 sm:h-10"></i>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-xs sm:text-sm">Pending Orders</p>
                    <p class="text-lg sm:text-2xl font-bold text-white">{{ $stats['pending_orders'] ?? 0 }}</p>
                </div>
                <div class="text-orange-400">
                    <i data-lucide="clock" class="w-6 h-6 sm:w-10 sm:h-10"></i>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-xs sm:text-sm">Your Points</p>
                    <p class="text-lg sm:text-2xl font-bold text-white">{{ $user->points ?? 0 }}</p>
                </div>
                <div class="text-purple-400">
                    <i data-lucide="star" class="w-6 h-6 sm:w-10 sm:h-10"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-8">
        <!-- Recent Orders -->
        <div class="lg:col-span-2">
            <div class="glass rounded-xl p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-yellow-400">Recent Orders</h2>
                    <a href="{{ route('user.orders') }}" class="text-xs sm:text-sm text-yellow-400 hover:text-yellow-300">View All →</a>
                </div>
                
                @if($recentOrders && $recentOrders->count() > 0)
                    <div class="space-y-3 sm:space-y-4">
                        @foreach($recentOrders as $order)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 bg-gray-800/50 rounded-lg space-y-2 sm:space-y-0">
                            <div class="flex items-center space-x-3 sm:space-x-4">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="gamepad-2" class="w-5 h-5 sm:w-6 sm:h-6 text-gray-400"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-sm sm:text-base truncate">{{ $order->game->name ?? 'Unknown Game' }}</p>
                                    <p class="text-xs sm:text-sm text-gray-400 truncate">{{ $order->denomination->name ?? 'Unknown Item' }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->created_at ? $order->created_at->diffForHumans() : 'Unknown date' }}</p>
                                </div>
                            </div>
                            <div class="text-left sm:text-right flex-shrink-0">
                                <p class="font-bold text-yellow-400 text-sm sm:text-base">Rp {{ number_format($order->total ?? 0, 0, ',', '.') }}</p>
                                <span class="inline-block px-2 py-1 text-xs rounded mt-1
                                    {{ $order->status == 'DELIVERED' ? 'bg-green-500' : '' }}
                                    {{ $order->status == 'PAID' ? 'bg-blue-500' : '' }}
                                    {{ in_array($order->status, ['PENDING', 'UNPAID']) ? 'bg-yellow-500' : '' }}
                                    {{ in_array($order->status, ['EXPIRED', 'FAILED']) ? 'bg-red-500' : '' }}
                                    text-white">
                                    {{ $order->status ?? 'UNKNOWN' }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 sm:py-8">
                        <i data-lucide="inbox" class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-4 text-gray-500"></i>
                        <p class="text-gray-400 text-sm sm:text-base">No orders yet</p>
                        <a href="{{ route('home') }}" class="mt-4 inline-block px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 text-sm sm:text-base">
                            Start Shopping
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Active Promos -->
        <div class="lg:col-span-1">
            <div class="glass rounded-xl p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-yellow-400">Active Promos</h2>
                    <a href="{{ route('user.promos') }}" class="text-xs sm:text-sm text-yellow-400 hover:text-yellow-300">View All →</a>
                </div>
                
                @if($activePromos && $activePromos->count() > 0)
                    <div class="space-y-3">
                        @foreach($activePromos as $promo)
                        <div class="p-3 bg-gradient-to-r from-yellow-400/10 to-orange-400/10 rounded-lg border border-yellow-400/20">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-yellow-400 text-sm">{{ $promo->code ?? 'NO CODE' }}</span>
                                <span class="text-xs bg-yellow-400 text-gray-900 px-2 py-1 rounded">
                                    @if($promo->type == 'percent')
                                        {{ $promo->value ?? 0 }}%
                                    @else
                                        Rp {{ number_format($promo->value ?? 0, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                            <p class="text-xs text-gray-400">
                                Min. Rp {{ number_format($promo->min_total ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500">
                                Valid until {{ $promo->ends_at ? $promo->ends_at->format('d M Y') : 'No expiry' }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i data-lucide="tag" class="w-8 h-8 mx-auto mb-2 text-gray-500"></i>
                        <p class="text-gray-400 text-sm">No active promos</p>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="glass rounded-xl p-4 sm:p-6 mt-4 sm:mt-6">
                <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                <div class="space-y-2 sm:space-y-3">
                    <a href="{{ route('home') }}" class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg hover:bg-gray-700/50 transition">
                        <span class="flex items-center">
                            <i data-lucide="shopping-cart" class="w-4 h-4 sm:w-5 sm:h-5 mr-3 text-yellow-400"></i>
                            <span class="text-sm sm:text-base">Top Up Games</span>
                        </span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('user.rewards.index') }}" class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-500/20 to-blue-500/20 border border-purple-500/30 rounded-lg hover:bg-purple-500/30 transition">
                        <span class="flex items-center">
                            <span class="w-4 h-4 sm:w-5 sm:h-5 mr-3 text-purple-400">💎</span>
                            <span class="text-sm sm:text-base">Redeem Rewards</span>
                        </span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('user.achievements.index') }}" class="flex items-center justify-between p-3 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border border-yellow-500/30 rounded-lg hover:bg-yellow-500/30 transition">
                        <span class="flex items-center">
                            <span class="w-4 h-4 sm:w-5 sm:h-5 mr-3 text-yellow-400">🏆</span>
                            <span class="text-sm sm:text-base">Achievements</span>
                        </span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('user.gaming-profile.index') }}" class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 border border-blue-500/30 rounded-lg hover:bg-blue-500/30 transition">
                        <span class="flex items-center">
                            <span class="w-4 h-4 sm:w-5 sm:h-5 mr-3 text-blue-400">🎮</span>
                            <span class="text-sm sm:text-base">Gaming Profile</span>
                        </span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('user.reviews') }}" class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg hover:bg-gray-700/50 transition">
                        <span class="flex items-center">
                            <i data-lucide="star" class="w-4 h-4 sm:w-5 sm:h-5 mr-3 text-yellow-400"></i>
                            <span class="text-sm sm:text-base">Write Review</span>
                        </span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                    </a>
                    <a href="{{ route('user.support') }}" class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg hover:bg-gray-700/50 transition">
                        <span class="flex items-center">
                            <i data-lucide="help-circle" class="w-4 h-4 sm:w-5 sm:h-5 mr-3 text-yellow-400"></i>
                            <span class="text-sm sm:text-base">Get Support</span>
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
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard initializing...');
    
    // Initialize Lucide icons with error handling
    function initLucideIcons() {
        try {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
                console.log('Lucide icons initialized successfully');
            } else {
                console.warn('Lucide not available, retrying...');
                setTimeout(initLucideIcons, 500);
            }
        } catch (error) {
            console.error('Error initializing Lucide icons:', error);
        }
    }
    
    // Force visibility of all dashboard elements
    try {
        const dashboardElements = document.querySelectorAll('.glass, .grid');
        dashboardElements.forEach(element => {
            element.style.opacity = '1';
            element.style.visibility = 'visible';
            if (element.classList.contains('grid')) {
                element.style.display = 'grid';
            }
        });
        console.log('Dashboard elements made visible:', dashboardElements.length);
    } catch (error) {
        console.error('Error making dashboard elements visible:', error);
    }
    
    // Animate elements on load with stagger (reduced motion aware)
    try {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (!prefersReducedMotion) {
            const cards = document.querySelectorAll('.glass');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50); // Reduced delay for better performance
            });
            console.log('Dashboard animations initialized for', cards.length, 'cards');
        } else {
            console.log('Reduced motion detected, skipping animations');
        }
    } catch (error) {
        console.error('Error initializing dashboard animations:', error);
    }
    
    // Initialize icons
    initLucideIcons();
    
    // Reinitialize icons after animations
    setTimeout(() => {
        initLucideIcons();
    }, 800);
});

// Fallback initialization with error handling
window.addEventListener('load', function() {
    try {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        // Final visibility check
        document.querySelectorAll('.glass').forEach(el => {
            el.style.opacity = '1';
            el.style.visibility = 'visible';
        });
        
        console.log('Dashboard fallback initialization completed');
    } catch (error) {
        console.error('Error in dashboard fallback initialization:', error);
    }
});

// Handle potential GSAP conflicts
if (typeof gsap !== 'undefined') {
    gsap.set('.glass', { opacity: 1, visibility: 'visible' });
}
</script>
@endpush
@endsection