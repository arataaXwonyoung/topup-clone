<?php

namespace App\Filament\Widgets;

use App\Models\Game;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PopularGamesTable extends BaseWidget
{
    protected static ?string $heading = 'Top Selling Games';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Game::withCount(['orders as revenue' => function (Builder $query) {
                    $query->select(\DB::raw('SUM(total)'))
                        ->whereIn('status', ['PAID', 'DELIVERED']);
                }])
                ->withCount('orders')
                ->orderByDesc('revenue')
                ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Total Orders')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('revenue')
                    ->label('Total Revenue')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_hot')
                    ->boolean()
                    ->label('Hot'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->paginated(false);
    }
}