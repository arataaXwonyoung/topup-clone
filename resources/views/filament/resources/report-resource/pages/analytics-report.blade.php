<x-filament-panels::page>
    @php
        $analytics = $this->getAnalyticsData();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Revenue Card -->
        <x-filament::section class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-green-500 rounded-lg">
                        <x-heroicon-o-banknotes class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($analytics['overview']['total_revenue'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Orders Card -->
        <x-filament::section class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-500 rounded-lg">
                        <x-heroicon-o-shopping-bag class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($analytics['overview']['total_orders']) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ number_format($analytics['overview']['successful_orders']) }} successful
                        </p>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Conversion Rate Card -->
        <x-filament::section class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-500 rounded-lg">
                        <x-heroicon-o-chart-bar class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Conversion Rate</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $analytics['overview']['conversion_rate'] }}%
                        </p>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Average Order Value Card -->
        <x-filament::section class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-500 rounded-lg">
                        <x-heroicon-o-calculator class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-orange-600 dark:text-orange-400">Avg Order Value</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($analytics['overview']['average_order_value'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Daily Revenue Chart -->
        <x-filament::section>
            <x-slot name="heading">
                Daily Revenue Trend
            </x-slot>
            
            <div class="p-4">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </x-filament::section>

        <!-- Top Games -->
        <x-filament::section>
            <x-slot name="heading">
                Top Games by Orders
            </x-slot>
            
            <div class="p-4">
                <div class="space-y-4">
                    @foreach($analytics['top_games'] as $index => $game)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center text-sm font-bold mr-3">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $game->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $game->orders_count }} orders</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-green-600">
                                    {{ number_format(($game->orders_count / $analytics['overview']['successful_orders']) * 100, 1) }}%
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue by Game -->
        <x-filament::section>
            <x-slot name="heading">
                Revenue by Game
            </x-slot>
            
            <div class="p-4">
                <div class="space-y-4">
                    @foreach($analytics['revenue_by_game'] as $gameRevenue)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $gameRevenue->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-green-600">
                                    Rp {{ number_format($gameRevenue->revenue, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-filament::section>

        <!-- Payment Methods -->
        <x-filament::section>
            <x-slot name="heading">
                Payment Methods Usage
            </x-slot>
            
            <div class="p-4">
                <canvas id="paymentChart" width="400" height="300"></canvas>
            </div>
        </x-filament::section>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: @json(array_column($analytics['daily_revenue'], 'date')),
                    datasets: [{
                        label: 'Revenue',
                        data: @json(array_column($analytics['daily_revenue'], 'revenue')),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Orders',
                        data: @json(array_column($analytics['daily_revenue'], 'orders')),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });

            // Payment Methods Chart
            const paymentCtx = document.getElementById('paymentChart').getContext('2d');
            const paymentChart = new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($analytics['payment_methods']->pluck('payment_method')->toArray()),
                    datasets: [{
                        data: @json($analytics['payment_methods']->pluck('count')->toArray()),
                        backgroundColor: [
                            '#3B82F6', // blue
                            '#10B981', // green
                            '#F59E0B', // yellow
                            '#EF4444', // red
                            '#8B5CF6', // purple
                            '#06B6D4', // cyan
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        </script>
    @endpush
</x-filament-panels::page>