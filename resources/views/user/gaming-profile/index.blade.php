@extends('layouts.app')

@section('title', 'Gaming Profile & Stats')

@push('styles')
<style>
    .gaming-profile-container {
        position: relative !important;
        z-index: 10 !important;
    }
    
    .stat-card {
        background: linear-gradient(135deg, rgba(30, 30, 40, 0.95), rgba(50, 50, 60, 0.8)) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        position: relative !important;
        z-index: 20 !important;
        transition: all 0.3s ease !important;
    }
    
    .stat-card:hover {
        transform: translateY(-4px) !important;
        border-color: rgba(255, 234, 0, 0.3) !important;
        box-shadow: 0 8px 25px rgba(255, 234, 0, 0.1) !important;
    }
    
    .game-stat-card {
        background: linear-gradient(135deg, rgba(30, 30, 40, 0.95), rgba(50, 50, 60, 0.8)) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        position: relative !important;
        z-index: 20 !important;
        transition: all 0.3s ease !important;
    }
    
    .game-stat-card:hover {
        transform: translateY(-2px) scale(1.02) !important;
        border-color: rgba(255, 234, 0, 0.4) !important;
    }
    
    .level-progress {
        background: linear-gradient(90deg, #6366f1, #8b5cf6) !important;
        height: 12px !important;
        border-radius: 6px !important;
        transition: width 1s ease !important;
    }
    
    .favorite-game {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2)) !important;
        border: 1px solid rgba(99, 102, 241, 0.3) !important;
    }
    
    .chart-container {
        position: relative !important;
        height: 300px !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 gaming-profile-container">
    <!-- Gaming Profile Header -->
    <div class="glass rounded-xl p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div class="flex items-center space-x-4">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-blue-600 rounded-full flex items-center justify-center">
                    <span class="text-2xl">🎮</span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-yellow-400">{{ $user->name }}'s Gaming Profile</h1>
                    <p class="text-gray-400">Level {{ $gamingLevel ?? 25 }} Gaming Enthusiast</p>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="text-sm text-gray-400">Gamer Since:</span>
                        <span class="text-sm text-white">{{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="text-center lg:text-right space-y-2">
                <div class="text-sm text-gray-400">Gaming Score</div>
                <div class="text-3xl font-bold text-purple-400">{{ $gamingScore ?? 8750 }}</div>
                <div class="text-sm text-gray-400">Top {{ $percentile ?? 15 }}% of players</div>
            </div>
        </div>
        
        <!-- Level Progress -->
        <div class="mt-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-400">Level Progress</span>
                <span class="text-sm text-purple-400">{{ $levelProgress ?? 65 }}% to Level {{ $gamingLevel + 1 ?? 26 }}</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-3">
                <div class="level-progress rounded-full h-3" style="width: {{ $levelProgress ?? 65 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stat-card rounded-xl p-6 text-center">
            <div class="text-3xl mb-2">🎯</div>
            <div class="text-2xl font-bold text-white">{{ $totalOrders ?? 48 }}</div>
            <div class="text-sm text-gray-400">Total Orders</div>
        </div>
        
        <div class="stat-card rounded-xl p-6 text-center">
            <div class="text-3xl mb-2">🎮</div>
            <div class="text-2xl font-bold text-white">{{ $uniqueGames ?? 12 }}</div>
            <div class="text-sm text-gray-400">Games Played</div>
        </div>
        
        <div class="stat-card rounded-xl p-6 text-center">
            <div class="text-3xl mb-2">💎</div>
            <div class="text-2xl font-bold text-white">{{ number_format($totalSpent ?? 2450000) }}</div>
            <div class="text-sm text-gray-400">Total Spent</div>
        </div>
        
        <div class="stat-card rounded-xl p-6 text-center">
            <div class="text-3xl mb-2">🔥</div>
            <div class="text-2xl font-bold text-white">{{ $streakDays ?? 7 }}</div>
            <div class="text-sm text-gray-400">Day Streak</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Favorite Games -->
        <div class="glass rounded-xl p-6">
            <h2 class="text-xl font-semibold text-yellow-400 mb-4">🏆 Favorite Games</h2>
            <div class="space-y-4">
                <div class="game-stat-card favorite-game rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold">ML</span>
                            </div>
                            <div>
                                <div class="font-semibold">Mobile Legends</div>
                                <div class="text-sm text-gray-400">18 orders • Rp 850,000 spent</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-purple-400">85%</div>
                            <div class="text-xs text-gray-400">of total orders</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-purple-500 rounded-full h-2" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="game-stat-card rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold">FF</span>
                            </div>
                            <div>
                                <div class="font-semibold">Free Fire</div>
                                <div class="text-sm text-gray-400">8 orders • Rp 320,000 spent</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-400">25%</div>
                            <div class="text-xs text-gray-400">of total orders</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 rounded-full h-2" style="width: 25%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="game-stat-card rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold">GI</span>
                            </div>
                            <div>
                                <div class="font-semibold">Genshin Impact</div>
                                <div class="text-sm text-gray-400">5 orders • Rp 450,000 spent</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-yellow-400">15%</div>
                            <div class="text-xs text-gray-400">of total orders</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-yellow-500 rounded-full h-2" style="width: 15%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spending Analytics -->
        <div class="glass rounded-xl p-6">
            <h2 class="text-xl font-semibold text-yellow-400 mb-4">📊 Spending Analytics</h2>
            <div class="chart-container mb-6">
                <canvas id="spendingChart" width="400" height="200"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-400">Rp {{ number_format($avgMonthlySpend ?? 150000) }}</div>
                    <div class="text-sm text-gray-400">Avg Monthly</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-400">Rp {{ number_format($biggestOrder ?? 85000) }}</div>
                    <div class="text-sm text-gray-400">Biggest Order</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gaming Achievements Timeline -->
    <div class="glass rounded-xl p-6 mt-8">
        <h2 class="text-xl font-semibold text-yellow-400 mb-6">🎖️ Gaming Milestones</h2>
        <div class="space-y-6">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold">🏆</span>
                </div>
                <div class="flex-1">
                    <div class="font-semibold">Reached Gold Tier</div>
                    <div class="text-sm text-gray-400">Unlocked exclusive benefits and 1.5x point multiplier</div>
                    <div class="text-xs text-gray-500">3 days ago</div>
                </div>
                <div class="text-yellow-400 font-semibold">+500 pts</div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold">🎮</span>
                </div>
                <div class="flex-1">
                    <div class="font-semibold">Game Explorer Achievement</div>
                    <div class="text-sm text-gray-400">Top-up completed for 10 different games</div>
                    <div class="text-xs text-gray-500">1 week ago</div>
                </div>
                <div class="text-purple-400 font-semibold">+300 pts</div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold">💎</span>
                </div>
                <div class="flex-1">
                    <div class="font-semibold">Big Spender Milestone</div>
                    <div class="text-sm text-gray-400">Total spending reached Rp 1,000,000</div>
                    <div class="text-xs text-gray-500">2 weeks ago</div>
                </div>
                <div class="text-blue-400 font-semibold">+400 pts</div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold">🔥</span>
                </div>
                <div class="flex-1">
                    <div class="font-semibold">Hot Streak Started</div>
                    <div class="text-sm text-gray-400">7-day consecutive purchasing streak</div>
                    <div class="text-xs text-gray-500">1 month ago</div>
                </div>
                <div class="text-green-400 font-semibold">+200 pts</div>
            </div>
        </div>
    </div>

    <!-- Personal Gaming Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        <div class="glass rounded-xl p-6">
            <h3 class="text-lg font-semibold text-yellow-400 mb-4">⏰ Activity Pattern</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Peak Hours</span>
                    <span class="text-sm text-white">7-9 PM</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Most Active Day</span>
                    <span class="text-sm text-white">Saturday</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Avg Session Time</span>
                    <span class="text-sm text-white">15 mins</span>
                </div>
            </div>
        </div>
        
        <div class="glass rounded-xl p-6">
            <h3 class="text-lg font-semibold text-yellow-400 mb-4">🎯 Preferences</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Favorite Genre</span>
                    <span class="text-sm text-white">MOBA</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Platform</span>
                    <span class="text-sm text-white">Mobile</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Payment Method</span>
                    <span class="text-sm text-white">E-Wallet</span>
                </div>
            </div>
        </div>
        
        <div class="glass rounded-xl p-6">
            <h3 class="text-lg font-semibold text-yellow-400 mb-4">📈 Growth</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">This Month</span>
                    <span class="text-sm text-green-400">+25%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Streak Record</span>
                    <span class="text-sm text-white">14 days</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Next Goal</span>
                    <span class="text-sm text-purple-400">Level 30</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Animate cards on load
    const cards = document.querySelectorAll('.stat-card, .game-stat-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Create spending chart
    const ctx = document.getElementById('spendingChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Monthly Spending',
                data: [120000, 90000, 150000, 180000, 160000, 200000],
                borderColor: '#fbbf24',
                backgroundColor: 'rgba(251, 191, 36, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fbbf24',
                pointBorderColor: '#f59e0b',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(75, 85, 99, 0.3)'
                    },
                    ticks: {
                        color: '#9ca3af'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(75, 85, 99, 0.3)'
                    },
                    ticks: {
                        color: '#9ca3af',
                        callback: function(value) {
                            return 'Rp ' + (value / 1000) + 'k';
                        }
                    }
                }
            },
            elements: {
                point: {
                    hoverRadius: 8
                }
            }
        }
    });
    
    // Animate level progress bar
    setTimeout(() => {
        const progressBar = document.querySelector('.level-progress');
        if (progressBar) {
            progressBar.style.width = '0%';
            setTimeout(() => {
                progressBar.style.width = '{{ $levelProgress ?? 65 }}%';
            }, 500);
        }
    }, 1000);
    
    // Animate favorite game progress bars
    setTimeout(() => {
        document.querySelectorAll('.game-stat-card .bg-purple-500, .game-stat-card .bg-green-500, .game-stat-card .bg-yellow-500').forEach((bar, index) => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, index * 200);
        });
    }, 1500);
});
</script>
@endpush
@endsection