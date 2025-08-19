<x-filament-panels::page>
    <form wire:submit="generate">
        {{ $this->form }}
        
        <div class="mt-6">
            <x-filament::button type="submit">
                Generate Report
            </x-filament::button>
        </div>
    </form>
    
    @php
        $reportData = $this->getReportData();
    @endphp
    
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-filament::card>
            <div class="text-sm font-medium text-gray-500">Total Revenue</div>
            <div class="mt-2 text-3xl font-bold text-primary-600">
                Rp {{ number_format($reportData['total_revenue'], 0, ',', '.') }}
            </div>
        </x-filament::card>
        
        <x-filament::card>
            <div class="text-sm font-medium text-gray-500">Total Orders</div>
            <div class="mt-2 text-3xl font-bold text-primary-600">
                {{ number_format($reportData['total_orders']) }}
            </div>
        </x-filament::card>
        
        <x-filament::card>
            <div class="text-sm font-medium text-gray-500">Average Order Value</div>
            <div class="mt-2 text-3xl font-bold text-primary-600">
                Rp {{ number_format($reportData['average_order'], 0, ',', '.') }}
            </div>
        </x-filament::card>
    </div>
    
    <div class="mt-8">
        <x-filament::card>
            <h3 class="text-lg font-semibold mb-4">Top Games by Revenue</h3>
            <div class="space-y-2">
                @foreach($reportData['top_games'] as $game)
                <div class="flex justify-between items-center">
                    <span>{{ $game->name }}</span>
                    <span class="font-semibold">Rp {{ number_format($game->orders_sum_total, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>