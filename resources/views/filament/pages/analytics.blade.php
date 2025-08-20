<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Date Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">From Date</label>
                    <input type="date" wire:model="dateFrom" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">To Date</label>
                    <input type="date" wire:model="dateTo" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div class="flex items-end">
                    <button wire:click="$refresh" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        Generate Report
                    </button>
                    <button wire:click="exportReport" class="ml-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Export
                    </button>
                </div>
            </div>
        </div>

        @php
            $data = $this->getAnalyticsData();
        @endphp

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
                <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
                <p class="text-3xl font-bold text-primary-600 mt-2">
                    Rp {{ number_format($data['totalRevenue'], 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
                <h3 class="text-sm font-medium text-gray-500">Average Order Value</h3>
                <p class="text-3xl font-bold text-primary-600 mt-2">
                    Rp {{ number_format($data['averageOrderValue'], 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
                <h3 class="text-sm font-medium text-gray-500">Success Rate</h3>
                <p class="text-3xl font-bold text-green-600 mt-2">
                    {{ number_format($data['successRate'], 1) }}%
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $data['successfulOrders'] }} / {{ $data['totalOrders'] }} orders
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
                <h3 class="text-sm font-medium text-gray-500">New Customers</h3>
                <p class="text-3xl font-bold text-primary-600 mt-2">
                    {{ number_format($data['newCustomers']) }}
                </p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Games Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">Top Games by Revenue</h3>
                <div class="space-y-3">
                    @foreach($data['topGames'] as $game)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-medium">{{ $game->name }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-primary-600 h-2 rounded-full" 
                                     style="width: {{ $data['totalRevenue'] > 0 ? ($game->orders_sum_total / $data['totalRevenue']) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span class="text-sm font-semibold">
                                Rp {{ number_format($game->orders_sum_total, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Payment Methods Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">Payment Methods</h3>
                <div class="space-y-3">
                    @foreach($data['paymentMethods'] as $method)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="px-2 py-1 text-xs rounded-lg
                                {{ $method->method == 'QRIS' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $method->method == 'VA' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $method->method == 'EWALLET' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ $method->method }}
                            </span>
                            <span class="text-sm">{{ $method->count }} transactions</span>
                        </div>
                        <span class="text-sm font-semibold">
                            Rp {{ number_format($method->total, 0, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Hourly Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
            <h3 class="text-lg font-semibold mb-4">Orders by Hour</h3>
            <div class="grid grid-cols-24 gap-1">
                @for($hour = 0; $hour < 24; $hour++)
                    @php
                        $hourData = $data['hourlyOrders']->firstWhere('hour', $hour);
                        $count = $hourData ? $hourData->count : 0;
                        $maxCount = $data['hourlyOrders']->max('count') ?: 1;
                        $height = ($count / $maxCount) * 100;
                    @endphp
                    <div class="flex flex-col items-center">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-t" style="height: 100px;">
                            <div class="w-full bg-primary-600 rounded-t transition-all duration-300 hover:bg-primary-700"
                                 style="height: {{ $height }}%; margin-top: {{ 100 - $height }}%;"
                                 title="{{ $hour }}:00 - {{ $count }} orders">
                            </div>
                        </div>
                        <span class="text-xs mt-1">{{ $hour }}</span>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</x-filament-panels::page>
