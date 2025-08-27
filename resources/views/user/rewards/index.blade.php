@extends('layouts.app')

@section('title', 'Rewards & Points')

@push('styles')
<style>
    .rewards-container {
        position: relative !important;
        z-index: 10 !important;
    }
    
    .reward-card {
        background: linear-gradient(135deg, rgba(30, 30, 40, 0.95), rgba(50, 50, 60, 0.8)) !important;
        border: 1px solid rgba(255, 234, 0, 0.2) !important;
        position: relative !important;
        z-index: 20 !important;
        transition: all 0.3s ease !important;
    }
    
    .reward-card:hover {
        transform: translateY(-4px) scale(1.02) !important;
        border-color: rgba(255, 234, 0, 0.5) !important;
        box-shadow: 0 8px 25px rgba(255, 234, 0, 0.2) !important;
    }
    
    .progress-bar {
        background: linear-gradient(90deg, #fbbf24, #f59e0b) !important;
        border-radius: 10px !important;
        height: 8px !important;
        transition: width 0.8s ease !important;
    }
    
    .tier-badge {
        background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
        color: #1f2937 !important;
        padding: 0.5rem 1rem !important;
        border-radius: 20px !important;
        font-weight: 700 !important;
        font-size: 0.875rem !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 rewards-container">
    <!-- Header Section -->
    <div class="glass rounded-xl p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div>
                <h1 class="text-3xl font-bold text-yellow-400 mb-2">💎 Rewards Center</h1>
                <p class="text-gray-400">Redeem your points for amazing rewards!</p>
            </div>
            <div class="text-center lg:text-right">
                <div class="text-sm text-gray-400">Your Points Balance</div>
                <div class="text-4xl font-bold text-yellow-400 mb-2">
                    {{ number_format($user->points ?? 0) }}
                </div>
                <span class="tier-badge">
                    {{ ucfirst($user->level ?? 'Bronze') }} Member
                </span>
            </div>
        </div>
    </div>

    <!-- Loyalty Progress -->
    <div class="glass rounded-xl p-6 mb-8">
        <h2 class="text-xl font-semibold text-yellow-400 mb-4">🏆 Loyalty Progress</h2>
        <div class="space-y-4">
            <!-- Current Level Progress -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-400">Progress to {{ $nextTier ?? 'Diamond' }}</span>
                    <span class="text-sm text-yellow-400">{{ $tierProgress ?? 75 }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-3">
                    <div class="progress-bar rounded-full h-3" style="width: {{ $tierProgress ?? 75 }}%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>{{ number_format($user->points ?? 0) }} points</span>
                    <span>{{ number_format($nextTierPoints ?? 10000) }} points needed</span>
                </div>
            </div>
            
            <!-- Tier Benefits -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                <div class="bg-gray-800/50 rounded-lg p-4 text-center {{ ($user->level ?? 'bronze') == 'bronze' ? 'border-2 border-yellow-400' : '' }}">
                    <div class="text-2xl mb-2">🥉</div>
                    <div class="font-semibold">Bronze</div>
                    <div class="text-xs text-gray-400">1x Points</div>
                </div>
                <div class="bg-gray-800/50 rounded-lg p-4 text-center {{ ($user->level ?? 'bronze') == 'silver' ? 'border-2 border-yellow-400' : '' }}">
                    <div class="text-2xl mb-2">🥈</div>
                    <div class="font-semibold">Silver</div>
                    <div class="text-xs text-gray-400">1.2x Points</div>
                </div>
                <div class="bg-gray-800/50 rounded-lg p-4 text-center {{ ($user->level ?? 'bronze') == 'gold' ? 'border-2 border-yellow-400' : '' }}">
                    <div class="text-2xl mb-2">🥇</div>
                    <div class="font-semibold">Gold</div>
                    <div class="text-xs text-gray-400">1.5x Points</div>
                </div>
                <div class="bg-gray-800/50 rounded-lg p-4 text-center {{ ($user->level ?? 'bronze') == 'diamond' ? 'border-2 border-yellow-400' : '' }}">
                    <div class="text-2xl mb-2">💎</div>
                    <div class="font-semibold">Diamond</div>
                    <div class="text-xs text-gray-400">2x Points</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reward Categories -->
    <div class="flex flex-wrap gap-2 mb-6">
        <button onclick="filterRewards('all')" class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold active-filter">
            All Rewards
        </button>
        <button onclick="filterRewards('games')" class="px-4 py-2 glass rounded-lg hover:bg-gray-700 transition">
            🎮 Game Credits
        </button>
        <button onclick="filterRewards('vouchers')" class="px-4 py-2 glass rounded-lg hover:bg-gray-700 transition">
            🎫 Vouchers
        </button>
        <button onclick="filterRewards('exclusive')" class="px-4 py-2 glass rounded-lg hover:bg-gray-700 transition">
            ⭐ Exclusive
        </button>
    </div>

    <!-- Rewards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="rewards-grid">
        <!-- Game Credit Rewards -->
        <div class="reward-card rounded-xl p-6 hover:transform hover:scale-105 transition" data-category="games">
            <div class="text-center mb-4">
                <div class="text-4xl mb-2">💰</div>
                <h3 class="text-lg font-semibold">Rp 50.000 Credit</h3>
                <p class="text-sm text-gray-400">Game top-up credit</p>
            </div>
            <div class="flex justify-between items-center mb-4">
                <span class="text-2xl font-bold text-yellow-400">5,000</span>
                <span class="text-sm text-gray-400">points</span>
            </div>
            <button onclick="redeemReward('credit_50k', 5000)" 
                    class="w-full py-3 {{ ($user->points ?? 0) >= 5000 ? 'bg-yellow-400 text-gray-900 hover:bg-yellow-500' : 'bg-gray-700 text-gray-400 cursor-not-allowed' }} 
                           rounded-lg font-semibold transition">
                {{ ($user->points ?? 0) >= 5000 ? 'Redeem Now' : 'Not Enough Points' }}
            </button>
        </div>

        <div class="reward-card rounded-xl p-6 hover:transform hover:scale-105 transition" data-category="games">
            <div class="text-center mb-4">
                <div class="text-4xl mb-2">💎</div>
                <h3 class="text-lg font-semibold">Rp 100.000 Credit</h3>
                <p class="text-sm text-gray-400">Game top-up credit</p>
            </div>
            <div class="flex justify-between items-center mb-4">
                <span class="text-2xl font-bold text-yellow-400">9,500</span>
                <span class="text-sm text-gray-400">points</span>
            </div>
            <button onclick="redeemReward('credit_100k', 9500)" 
                    class="w-full py-3 {{ ($user->points ?? 0) >= 9500 ? 'bg-yellow-400 text-gray-900 hover:bg-yellow-500' : 'bg-gray-700 text-gray-400 cursor-not-allowed' }} 
                           rounded-lg font-semibold transition">
                {{ ($user->points ?? 0) >= 9500 ? 'Redeem Now' : 'Not Enough Points' }}
            </button>
        </div>

        <!-- Voucher Rewards -->
        <div class="reward-card rounded-xl p-6 hover:transform hover:scale-105 transition" data-category="vouchers">
            <div class="text-center mb-4">
                <div class="text-4xl mb-2">☕</div>
                <h3 class="text-lg font-semibold">Starbucks Voucher</h3>
                <p class="text-sm text-gray-400">Rp 50.000 coffee voucher</p>
            </div>
            <div class="flex justify-between items-center mb-4">
                <span class="text-2xl font-bold text-yellow-400">6,000</span>
                <span class="text-sm text-gray-400">points</span>
            </div>
            <button onclick="redeemReward('starbucks_50k', 6000)" 
                    class="w-full py-3 {{ ($user->points ?? 0) >= 6000 ? 'bg-yellow-400 text-gray-900 hover:bg-yellow-500' : 'bg-gray-700 text-gray-400 cursor-not-allowed' }} 
                           rounded-lg font-semibold transition">
                {{ ($user->points ?? 0) >= 6000 ? 'Redeem Now' : 'Not Enough Points' }}
            </button>
        </div>

        <div class="reward-card rounded-xl p-6 hover:transform hover:scale-105 transition" data-category="vouchers">
            <div class="text-center mb-4">
                <div class="text-4xl mb-2">🍔</div>
                <h3 class="text-lg font-semibold">McDonald's Voucher</h3>
                <p class="text-sm text-gray-400">Rp 75.000 food voucher</p>
            </div>
            <div class="flex justify-between items-center mb-4">
                <span class="text-2xl font-bold text-yellow-400">7,500</span>
                <span class="text-sm text-gray-400">points</span>
            </div>
            <button onclick="redeemReward('mcdonalds_75k', 7500)" 
                    class="w-full py-3 {{ ($user->points ?? 0) >= 7500 ? 'bg-yellow-400 text-gray-900 hover:bg-yellow-500' : 'bg-gray-700 text-gray-400 cursor-not-allowed' }} 
                           rounded-lg font-semibold transition">
                {{ ($user->points ?? 0) >= 7500 ? 'Redeem Now' : 'Not Enough Points' }}
            </button>
        </div>

        <!-- Exclusive Rewards -->
        <div class="reward-card rounded-xl p-6 hover:transform hover:scale-105 transition" data-category="exclusive">
            <div class="text-center mb-4">
                <div class="text-4xl mb-2">👑</div>
                <h3 class="text-lg font-semibold">VIP Status</h3>
                <p class="text-sm text-gray-400">30 days VIP membership</p>
            </div>
            <div class="flex justify-between items-center mb-4">
                <span class="text-2xl font-bold text-yellow-400">15,000</span>
                <span class="text-sm text-gray-400">points</span>
            </div>
            <button onclick="redeemReward('vip_30days', 15000)" 
                    class="w-full py-3 {{ ($user->points ?? 0) >= 15000 ? 'bg-yellow-400 text-gray-900 hover:bg-yellow-500' : 'bg-gray-700 text-gray-400 cursor-not-allowed' }} 
                           rounded-lg font-semibold transition">
                {{ ($user->points ?? 0) >= 15000 ? 'Redeem Now' : 'Not Enough Points' }}
            </button>
        </div>

        <div class="reward-card rounded-xl p-6 hover:transform hover:scale-105 transition" data-category="exclusive">
            <div class="text-center mb-4">
                <div class="text-4xl mb-2">🎁</div>
                <h3 class="text-lg font-semibold">Mystery Box</h3>
                <p class="text-sm text-gray-400">Random premium rewards</p>
            </div>
            <div class="flex justify-between items-center mb-4">
                <span class="text-2xl font-bold text-yellow-400">10,000</span>
                <span class="text-sm text-gray-400">points</span>
            </div>
            <button onclick="redeemReward('mystery_box', 10000)" 
                    class="w-full py-3 {{ ($user->points ?? 0) >= 10000 ? 'bg-yellow-400 text-gray-900 hover:bg-yellow-500' : 'bg-gray-700 text-gray-400 cursor-not-allowed' }} 
                           rounded-lg font-semibold transition">
                {{ ($user->points ?? 0) >= 10000 ? 'Redeem Now' : 'Not Enough Points' }}
            </button>
        </div>
    </div>

    <!-- How to Earn Points -->
    <div class="glass rounded-xl p-6 mt-8">
        <h2 class="text-xl font-semibold text-yellow-400 mb-4">💡 How to Earn Points</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-800/50 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">🛒</div>
                <div class="font-semibold">Make Purchases</div>
                <div class="text-sm text-gray-400">1 point per Rp 1,000</div>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">⭐</div>
                <div class="font-semibold">Write Reviews</div>
                <div class="text-sm text-gray-400">50 points per review</div>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">🎉</div>
                <div class="font-semibold">Refer Friends</div>
                <div class="text-sm text-gray-400">500 points per referral</div>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">📱</div>
                <div class="font-semibold">Daily Login</div>
                <div class="text-sm text-gray-400">10-100 points daily</div>
            </div>
        </div>
    </div>

    <!-- Redemption History -->
    <div class="glass rounded-xl p-6 mt-8">
        <h2 class="text-xl font-semibold text-yellow-400 mb-4">📋 Recent Redemptions</h2>
        <div class="space-y-3">
            <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                <div>
                    <div class="font-semibold">Rp 50.000 Game Credit</div>
                    <div class="text-sm text-gray-400">Redeemed 2 days ago</div>
                </div>
                <div class="text-right">
                    <div class="text-red-400 font-semibold">-5,000 points</div>
                    <div class="text-xs text-green-400">✓ Delivered</div>
                </div>
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                <div>
                    <div class="font-semibold">Starbucks Voucher</div>
                    <div class="text-sm text-gray-400">Redeemed 1 week ago</div>
                </div>
                <div class="text-right">
                    <div class="text-red-400 font-semibold">-6,000 points</div>
                    <div class="text-xs text-green-400">✓ Delivered</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Filter rewards by category
function filterRewards(category) {
    const cards = document.querySelectorAll('[data-category]');
    const buttons = document.querySelectorAll('button[onclick^="filterRewards"]');
    
    // Update button styles
    buttons.forEach(btn => {
        btn.classList.remove('bg-yellow-400', 'text-gray-900', 'active-filter');
        btn.classList.add('glass', 'hover:bg-gray-700');
    });
    
    event.target.classList.add('bg-yellow-400', 'text-gray-900', 'active-filter');
    event.target.classList.remove('glass', 'hover:bg-gray-700');
    
    // Filter cards
    cards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.opacity = '1';
            }, 100);
        } else {
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.display = 'none';
            }, 300);
        }
    });
}

// Redeem reward function
async function redeemReward(rewardType, pointsCost) {
    const userPoints = {{ $user->points ?? 0 }};
    
    if (userPoints < pointsCost) {
        showToast('Not enough points to redeem this reward!', 'error');
        return;
    }
    
    if (!confirm(`Redeem this reward for ${pointsCost.toLocaleString()} points?`)) {
        return;
    }
    
    const loadingToast = showToast('Processing redemption...', 'info', 0);
    
    try {
        const response = await fetch('/user/rewards/redeem', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                reward_type: rewardType,
                points_cost: pointsCost
            })
        });
        
        const data = await response.json();
        
        if (loadingToast) document.body.removeChild(loadingToast);
        
        if (data.success) {
            showToast('Reward redeemed successfully! 🎉', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Redemption failed', 'error');
        }
    } catch (error) {
        if (loadingToast) document.body.removeChild(loadingToast);
        showToast('System error occurred', 'error');
        console.error('Redemption error:', error);
    }
}

// Toast notification system
function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full`;
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="w-5 h-5"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }, 10);
    
    if (duration > 0) {
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, duration);
    }
    
    return toast;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Animate rewards cards on load
    const cards = document.querySelectorAll('.reward-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush
@endsection