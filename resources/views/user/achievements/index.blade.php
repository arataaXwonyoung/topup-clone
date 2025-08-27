@extends('layouts.app')

@section('title', 'Achievements & Badges')

@push('styles')
<style>
    .achievements-container {
        position: relative !important;
        z-index: 10 !important;
    }
    
    .achievement-card {
        background: linear-gradient(135deg, rgba(30, 30, 40, 0.95), rgba(50, 50, 60, 0.8)) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        position: relative !important;
        z-index: 20 !important;
        transition: all 0.3s ease !important;
        overflow: hidden !important;
    }
    
    .achievement-card.unlocked {
        border-color: rgba(255, 234, 0, 0.5) !important;
        box-shadow: 0 4px 20px rgba(255, 234, 0, 0.2) !important;
    }
    
    .achievement-card.unlocked:hover {
        transform: translateY(-4px) scale(1.02) !important;
        box-shadow: 0 8px 30px rgba(255, 234, 0, 0.3) !important;
    }
    
    .achievement-card.locked {
        opacity: 0.6 !important;
        filter: grayscale(70%) !important;
    }
    
    .badge-icon {
        width: 80px !important;
        height: 80px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 2rem !important;
        margin: 0 auto 1rem !important;
        position: relative !important;
    }
    
    .badge-icon.unlocked {
        background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
        box-shadow: 0 0 20px rgba(251, 191, 36, 0.4) !important;
        animation: badgeGlow 2s ease-in-out infinite alternate !important;
    }
    
    .badge-icon.locked {
        background: rgba(75, 85, 99, 0.5) !important;
        border: 2px dashed rgba(156, 163, 175, 0.5) !important;
    }
    
    @keyframes badgeGlow {
        from { box-shadow: 0 0 20px rgba(251, 191, 36, 0.4); }
        to { box-shadow: 0 0 30px rgba(251, 191, 36, 0.6); }
    }
    
    .progress-ring {
        width: 100px !important;
        height: 100px !important;
        transform: rotate(-90deg) !important;
    }
    
    .progress-ring-circle {
        fill: none !important;
        stroke-width: 6 !important;
        stroke-linecap: round !important;
        transition: stroke-dashoffset 0.8s ease !important;
    }
    
    .category-badge {
        background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
        color: white !important;
        padding: 0.25rem 0.75rem !important;
        border-radius: 12px !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 achievements-container">
    <!-- Header Section -->
    <div class="glass rounded-xl p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div>
                <h1 class="text-3xl font-bold text-yellow-400 mb-2">🏆 Achievements & Badges</h1>
                <p class="text-gray-400">Unlock badges by completing challenges and milestones!</p>
            </div>
            <div class="text-center lg:text-right">
                <div class="text-sm text-gray-400">Unlocked Achievements</div>
                <div class="text-3xl font-bold text-yellow-400">
                    {{ $unlockedCount ?? 8 }}/{{ $totalCount ?? 24 }}
                </div>
                <div class="text-sm text-gray-400">
                    {{ number_format((($unlockedCount ?? 8) / ($totalCount ?? 24)) * 100, 1) }}% Complete
                </div>
            </div>
        </div>
        
        <!-- Overall Progress Bar -->
        <div class="mt-4">
            <div class="w-full bg-gray-700 rounded-full h-3">
                <div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full h-3 transition-all duration-1000" 
                     style="width: {{ number_format((($unlockedCount ?? 8) / ($totalCount ?? 24)) * 100, 1) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Achievement Categories -->
    <div class="flex flex-wrap gap-2 mb-6">
        <button onclick="filterAchievements('all')" class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold active-filter">
            All Badges
        </button>
        <button onclick="filterAchievements('purchases')" class="px-4 py-2 glass rounded-lg hover:bg-gray-700 transition">
            🛒 Purchases
        </button>
        <button onclick="filterAchievements('loyalty')" class="px-4 py-2 glass rounded-lg hover:bg-gray-700 transition">
            💎 Loyalty
        </button>
        <button onclick="filterAchievements('social')" class="px-4 py-2 glass rounded-lg hover:bg-gray-700 transition">
            👥 Social
        </button>
        <button onclick="filterAchievements('special')" class="px-4 py-2 glass rounded-lg hover:bg-gray-700 transition">
            ⭐ Special
        </button>
    </div>

    <!-- Achievements Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="achievements-grid">
        
        <!-- Purchase Achievements -->
        <div class="achievement-card unlocked rounded-xl p-6" data-category="purchases">
            <span class="category-badge">Purchases</span>
            <div class="badge-icon unlocked">🛍️</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">First Purchase</h3>
                <p class="text-sm text-gray-400 mb-3">Make your first top-up order</p>
                <div class="text-xs text-green-400 font-semibold">✓ UNLOCKED</div>
                <div class="text-xs text-gray-500">+100 points</div>
            </div>
        </div>

        <div class="achievement-card unlocked rounded-xl p-6" data-category="purchases">
            <span class="category-badge">Purchases</span>
            <div class="badge-icon unlocked">💰</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Big Spender</h3>
                <p class="text-sm text-gray-400 mb-3">Spend over Rp 1,000,000</p>
                <div class="text-xs text-green-400 font-semibold">✓ UNLOCKED</div>
                <div class="text-xs text-gray-500">+500 points</div>
            </div>
        </div>

        <div class="achievement-card locked rounded-xl p-6" data-category="purchases">
            <span class="category-badge">Purchases</span>
            <div class="badge-icon locked">🏆</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Million Club</h3>
                <p class="text-sm text-gray-400 mb-3">Spend over Rp 5,000,000</p>
                <div class="text-xs text-yellow-400">Progress: Rp 1,250,000 / Rp 5,000,000</div>
                <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                    <div class="bg-yellow-400 rounded-full h-2" style="width: 25%"></div>
                </div>
            </div>
        </div>

        <!-- Loyalty Achievements -->
        <div class="achievement-card unlocked rounded-xl p-6" data-category="loyalty">
            <span class="category-badge">Loyalty</span>
            <div class="badge-icon unlocked">⭐</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Loyal Customer</h3>
                <p class="text-sm text-gray-400 mb-3">Complete 10 successful orders</p>
                <div class="text-xs text-green-400 font-semibold">✓ UNLOCKED</div>
                <div class="text-xs text-gray-500">+200 points</div>
            </div>
        </div>

        <div class="achievement-card unlocked rounded-xl p-6" data-category="loyalty">
            <span class="category-badge">Loyalty</span>
            <div class="badge-icon unlocked">🥇</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Gold Member</h3>
                <p class="text-sm text-gray-400 mb-3">Reach Gold tier status</p>
                <div class="text-xs text-green-400 font-semibold">✓ UNLOCKED</div>
                <div class="text-xs text-gray-500">+300 points</div>
            </div>
        </div>

        <div class="achievement-card locked rounded-xl p-6" data-category="loyalty">
            <span class="category-badge">Loyalty</span>
            <div class="badge-icon locked">💎</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Diamond Elite</h3>
                <p class="text-sm text-gray-400 mb-3">Reach Diamond tier status</p>
                <div class="text-xs text-yellow-400">Progress: Gold → Diamond</div>
                <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                    <div class="bg-yellow-400 rounded-full h-2" style="width: 60%"></div>
                </div>
            </div>
        </div>

        <!-- Social Achievements -->
        <div class="achievement-card unlocked rounded-xl p-6" data-category="social">
            <span class="category-badge">Social</span>
            <div class="badge-icon unlocked">📝</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Reviewer</h3>
                <p class="text-sm text-gray-400 mb-3">Write 5 product reviews</p>
                <div class="text-xs text-green-400 font-semibold">✓ UNLOCKED</div>
                <div class="text-xs text-gray-500">+150 points</div>
            </div>
        </div>

        <div class="achievement-card unlocked rounded-xl p-6" data-category="social">
            <span class="category-badge">Social</span>
            <div class="badge-icon unlocked">👥</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Referral Master</h3>
                <p class="text-sm text-gray-400 mb-3">Refer 3 friends successfully</p>
                <div class="text-xs text-green-400 font-semibold">✓ UNLOCKED</div>
                <div class="text-xs text-gray-500">+250 points</div>
            </div>
        </div>

        <div class="achievement-card locked rounded-xl p-6" data-category="social">
            <span class="category-badge">Social</span>
            <div class="badge-icon locked">🌟</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Influencer</h3>
                <p class="text-sm text-gray-400 mb-3">Refer 10+ friends</p>
                <div class="text-xs text-yellow-400">Progress: 3 / 10 referrals</div>
                <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                    <div class="bg-yellow-400 rounded-full h-2" style="width: 30%"></div>
                </div>
            </div>
        </div>

        <!-- Special Achievements -->
        <div class="achievement-card unlocked rounded-xl p-6" data-category="special">
            <span class="category-badge">Special</span>
            <div class="badge-icon unlocked">🎂</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Birthday Bonus</h3>
                <p class="text-sm text-gray-400 mb-3">Make purchase on birthday</p>
                <div class="text-xs text-green-400 font-semibold">✓ UNLOCKED</div>
                <div class="text-xs text-gray-500">+100 points</div>
            </div>
        </div>

        <div class="achievement-card unlocked rounded-xl p-6" data-category="special">
            <span class="category-badge">Special</span>
            <div class="badge-icon unlocked">🎮</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Game Explorer</h3>
                <p class="text-sm text-gray-400 mb-3">Top-up 5 different games</p>
                <div class="text-xs text-green-400 font-semibold">✓ UNLOCKED</div>
                <div class="text-xs text-gray-500">+200 points</div>
            </div>
        </div>

        <div class="achievement-card locked rounded-xl p-6" data-category="special">
            <span class="category-badge">Special</span>
            <div class="badge-icon locked">🚀</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Speed Demon</h3>
                <p class="text-sm text-gray-400 mb-3">Complete 5 orders in 1 day</p>
                <div class="text-xs text-yellow-400">Progress: 2 / 5 orders today</div>
                <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                    <div class="bg-yellow-400 rounded-full h-2" style="width: 40%"></div>
                </div>
            </div>
        </div>

        <!-- More achievements... -->
        <div class="achievement-card locked rounded-xl p-6" data-category="purchases">
            <span class="category-badge">Purchases</span>
            <div class="badge-icon locked">🔥</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Hot Streak</h3>
                <p class="text-sm text-gray-400 mb-3">Make purchases 7 days in a row</p>
                <div class="text-xs text-yellow-400">Progress: 3 / 7 days</div>
                <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                    <div class="bg-yellow-400 rounded-full h-2" style="width: 43%"></div>
                </div>
            </div>
        </div>

        <div class="achievement-card locked rounded-xl p-6" data-category="loyalty">
            <span class="category-badge">Loyalty</span>
            <div class="badge-icon locked">🎯</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Perfect Record</h3>
                <p class="text-sm text-gray-400 mb-3">50 orders with 5-star reviews</p>
                <div class="text-xs text-yellow-400">Progress: 12 / 50 orders</div>
                <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                    <div class="bg-yellow-400 rounded-full h-2" style="width: 24%"></div>
                </div>
            </div>
        </div>

        <div class="achievement-card locked rounded-xl p-6" data-category="special">
            <span class="category-badge">Special</span>
            <div class="badge-icon locked">🌙</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Night Owl</h3>
                <p class="text-sm text-gray-400 mb-3">Make 10 orders after midnight</p>
                <div class="text-xs text-yellow-400">Progress: 4 / 10 midnight orders</div>
                <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                    <div class="bg-yellow-400 rounded-full h-2" style="width: 40%"></div>
                </div>
            </div>
        </div>

        <div class="achievement-card locked rounded-xl p-6" data-category="special">
            <span class="category-badge">Special</span>
            <div class="badge-icon locked">👑</div>
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Legendary</h3>
                <p class="text-sm text-gray-400 mb-3">Complete all other achievements</p>
                <div class="text-xs text-yellow-400">Progress: 8 / 23 achievements</div>
                <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                    <div class="bg-yellow-400 rounded-full h-2" style="width: 35%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Unlocked -->
    <div class="glass rounded-xl p-6 mt-8">
        <h2 class="text-xl font-semibold text-yellow-400 mb-4">🎉 Recently Unlocked</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-gray-800/50 rounded-lg p-4 flex items-center space-x-4">
                <div class="badge-icon unlocked" style="width: 50px; height: 50px; font-size: 1.5rem;">👥</div>
                <div>
                    <div class="font-semibold">Referral Master</div>
                    <div class="text-sm text-gray-400">Unlocked 3 days ago</div>
                </div>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-4 flex items-center space-x-4">
                <div class="badge-icon unlocked" style="width: 50px; height: 50px; font-size: 1.5rem;">🥇</div>
                <div>
                    <div class="font-semibold">Gold Member</div>
                    <div class="text-sm text-gray-400">Unlocked 1 week ago</div>
                </div>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-4 flex items-center space-x-4">
                <div class="badge-icon unlocked" style="width: 50px; height: 50px; font-size: 1.5rem;">🎮</div>
                <div>
                    <div class="font-semibold">Game Explorer</div>
                    <div class="text-sm text-gray-400">Unlocked 2 weeks ago</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Filter achievements by category
function filterAchievements(category) {
    const cards = document.querySelectorAll('[data-category]');
    const buttons = document.querySelectorAll('button[onclick^="filterAchievements"]');
    
    // Update button styles
    buttons.forEach(btn => {
        btn.classList.remove('bg-yellow-400', 'text-gray-900', 'active-filter');
        btn.classList.add('glass', 'hover:bg-gray-700');
    });
    
    event.target.classList.add('bg-yellow-400', 'text-gray-900', 'active-filter');
    event.target.classList.remove('glass', 'hover:bg-gray-700');
    
    // Filter cards
    cards.forEach((card, index) => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.4s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        } else {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.display = 'none';
            }, 300);
        }
    });
}

// Simulate achievement unlock animation
function simulateUnlock(achievementName) {
    // Create achievement unlock notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 px-8 py-6 rounded-xl shadow-2xl z-50 text-center';
    notification.innerHTML = `
        <div class="text-4xl mb-2">🎉</div>
        <div class="text-xl font-bold mb-2">Achievement Unlocked!</div>
        <div class="text-lg">${achievementName}</div>
        <div class="text-sm mt-2">+250 points earned</div>
    `;
    
    document.body.appendChild(notification);
    
    // Add confetti effect
    if (typeof confetti !== 'undefined') {
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    }
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Animate achievement cards on load
    const cards = document.querySelectorAll('.achievement-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 50);
    });
    
    // Add click handlers for unlocked achievements
    document.querySelectorAll('.achievement-card.unlocked').forEach(card => {
        card.addEventListener('click', function() {
            this.style.transform = 'scale(1.05)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
        });
    });
    
    // Simulate progress updates (demo)
    setTimeout(() => {
        const progressBars = document.querySelectorAll('.achievement-card.locked .bg-yellow-400');
        progressBars.forEach((bar, index) => {
            setTimeout(() => {
                const currentWidth = parseFloat(bar.style.width);
                if (currentWidth < 90) {
                    bar.style.width = Math.min(currentWidth + 5, 90) + '%';
                }
            }, index * 500);
        });
    }, 2000);
});
</script>
@endpush
@endsection