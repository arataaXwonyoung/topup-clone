<?php

namespace App\Filament\Widgets;

use App\Models\Game;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopGamesWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Performing Games';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Game::query()
                    ->select([
                        'games.*',
                        \DB::raw('COUNT(orders.id) as orders_count'),
                        \DB::raw('SUM(CASE WHEN orders.status IN ("PAID", "DELIVERED") THEN orders.total ELSE 0 END) as total_revenue'),
                        \DB::raw('AVG(CASE WHEN orders.status IN ("PAID", "DELIVERED") THEN orders.total ELSE NULL END) as avg_order_value'),
                    ])
                    ->leftJoin('orders', 'games.id', '=', 'orders.game_id')
                    ->where('games.is_active', true)
                    ->where(function($query) {
                        $query->whereBetween('orders.created_at', [now()->subDays(30), now()])
                              ->orWhereNull('orders.created_at');
                    })
                    ->groupBy('games.id', 'games.name', 'games.cover_path', 'games.is_active')
                    ->having('orders_count', '>', 0)
                    ->orderByDesc('total_revenue')
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->state(function ($livewire, $record) {
                        static $rank = 0;
                        return ++$rank;
                    })
                    ->alignCenter(),
                    
                Tables\Columns\ImageColumn::make('cover_path')
                    ->label('Game')
                    ->circular()
                    ->size(40),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Orders')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('avg_order_value')
                    ->label('Avg Order')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('performance_indicator')
                    ->label('Trend')
                    ->state(function ($record) {
                        // Calculate trend compared to previous period
                        $currentRevenue = $record->total_revenue;
                        $previousRevenue = Order::where('game_id', $record->id)
                            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
                            ->whereIn('status', ['PAID', 'DELIVERED'])
                            ->sum('total');
                            
                        if ($previousRevenue == 0) return 'new';
                        
                        $growth = (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
                        
                        if ($growth > 20) return '📈 Hot';
                        if ($growth > 0) return '↗️ Up';
                        if ($growth < -20) return '📉 Down';
                        return '➡️ Stable';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match(true) {
                        str_contains($state, 'Hot') => 'danger',
                        str_contains($state, 'Up') => 'success', 
                        str_contains($state, 'Down') => 'warning',
                        str_contains($state, 'new') => 'info',
                        default => 'gray'
                    }),
            ])
            ->paginated([10, 25])
            ->poll('5m');
    }
    
    public function getTableRecordKey($record): string
    {
        return $record->id;
    }
}