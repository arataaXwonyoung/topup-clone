<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                System Status & API Providers
            </div>
        </x-slot>

        @php
            $status = $this->getSystemStatus();
        @endphp

        <div class="space-y-6">
            <!-- API Providers Status -->
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">API Providers</h4>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-2xl font-bold text-blue-600">{{ $status['api_providers']['total'] }}</p>
                                <p class="text-xs text-blue-600/70">Total Providers</p>
                            </div>
                            <x-heroicon-o-building-office class="w-8 h-8 text-blue-500/30" />
                        </div>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-2xl font-bold text-green-600">{{ $status['api_providers']['active'] }}</p>
                                <p class="text-xs text-green-600/70">Active</p>
                            </div>
                            <x-heroicon-o-check-circle class="w-8 h-8 text-green-500/30" />
                        </div>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-2xl font-bold text-purple-600">{{ $status['api_providers']['payment_providers'] }}</p>
                                <p class="text-xs text-purple-600/70">Payment</p>
                            </div>
                            <x-heroicon-o-credit-card class="w-8 h-8 text-purple-500/30" />
                        </div>
                    </div>

                    <div class="bg-orange-50 dark:bg-orange-900/20 p-3 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-2xl font-bold text-orange-600">{{ $status['api_providers']['topup_providers'] }}</p>
                                <p class="text-xs text-orange-600/70">Top-up</p>
                            </div>
                            <x-heroicon-o-arrow-up-circle class="w-8 h-8 text-orange-500/30" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Activity -->
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Recent Activity</h4>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-2xl font-bold text-yellow-600">{{ $status['recent_activity']['pending_orders'] }}</p>
                                <p class="text-xs text-yellow-600/70">Pending Orders</p>
                            </div>
                            <x-heroicon-o-clock class="w-8 h-8 text-yellow-500/30" />
                        </div>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-2xl font-bold text-blue-600">{{ $status['recent_activity']['processing_orders'] }}</p>
                                <p class="text-xs text-blue-600/70">Processing</p>
                            </div>
                            <x-heroicon-o-arrow-path class="w-8 h-8 text-blue-500/30" />
                        </div>
                    </div>

                    <div class="bg-orange-50 dark:bg-orange-900/20 p-3 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-2xl font-bold text-orange-600">{{ $status['recent_activity']['pending_payments'] }}</p>
                                <p class="text-xs text-orange-600/70">Pending Payments</p>
                            </div>
                            <x-heroicon-o-banknotes class="w-8 h-8 text-orange-500/30" />
                        </div>
                    </div>

                    <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-2xl font-bold text-red-600">{{ $status['recent_activity']['failed_payments_today'] }}</p>
                                <p class="text-xs text-red-600/70">Failed Today</p>
                            </div>
                            <x-heroicon-o-x-circle class="w-8 h-8 text-red-500/30" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">System Health</h4>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-circle-stack class="w-5 h-5 text-gray-500" />
                            <span class="text-sm font-medium">Database</span>
                        </div>
                        <div class="flex items-center gap-1">
                            @if($status['health_checks']['database'])
                                <x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />
                                <span class="text-xs text-green-600">Connected</span>
                            @else
                                <x-heroicon-s-x-circle class="w-5 h-5 text-red-500" />
                                <span class="text-xs text-red-600">Error</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-folder class="w-5 h-5 text-gray-500" />
                            <span class="text-sm font-medium">Storage</span>
                        </div>
                        <div class="flex items-center gap-1">
                            @if($status['health_checks']['storage'])
                                <x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />
                                <span class="text-xs text-green-600">Available</span>
                            @else
                                <x-heroicon-s-x-circle class="w-5 h-5 text-red-500" />
                                <span class="text-xs text-red-600">Error</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-queue-list class="w-5 h-5 text-gray-500" />
                            <span class="text-sm font-medium">Queue</span>
                        </div>
                        <div class="flex items-center gap-1">
                            @if($status['health_checks']['queue'])
                                <x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />
                                <span class="text-xs text-green-600">Running</span>
                            @else
                                <x-heroicon-s-x-circle class="w-5 h-5 text-red-500" />
                                <span class="text-xs text-red-600">Error</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ \App\Filament\Resources\ApiProviderResource::getUrl('index') }}" 
                       class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded-md transition-colors">
                        <x-heroicon-o-cog-6-tooth class="w-4 h-4 mr-1" />
                        Manage Providers
                    </a>
                    <a href="{{ \App\Filament\Resources\OrderResource::getUrl('index', ['tableFilters[status][values][0]' => 'PENDING']) }}" 
                       class="inline-flex items-center px-3 py-1.5 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 text-xs font-medium rounded-md transition-colors">
                        <x-heroicon-o-clock class="w-4 h-4 mr-1" />
                        View Pending Orders
                    </a>
                    <a href="{{ \App\Filament\Resources\PaymentResource::getUrl('index', ['tableFilters[status][values][0]' => 'pending']) }}" 
                       class="inline-flex items-center px-3 py-1.5 bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-medium rounded-md transition-colors">
                        <x-heroicon-o-banknotes class="w-4 h-4 mr-1" />
                        View Payments
                    </a>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>