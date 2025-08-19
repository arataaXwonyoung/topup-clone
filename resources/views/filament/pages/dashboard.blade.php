<x-filament-panels::page>
    <x-filament::section>
        <div class="text-center py-8">
            <h2 class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                Welcome to Takapedia Admin Dashboard
            </h2>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage your game top-up platform efficiently
            </p>
        </div>
    </x-filament::section>

    <div class="fi-wi-stats-overview-container">
        @livewire(\App\Filament\Widgets\StatsOverview::class)
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <div>
            @livewire(\App\Filament\Widgets\RevenueChart::class)
        </div>
        <div>
            @livewire(\App\Filament\Widgets\PopularGames::class)
        </div>
    </div>

    <div class="mt-6">
        @livewire(\App\Filament\Widgets\LatestOrders::class)
    </div>
</x-filament-panels::page>