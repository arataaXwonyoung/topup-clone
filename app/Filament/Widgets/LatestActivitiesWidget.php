<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\Article;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class LatestActivitiesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getLatestActivitiesQuery())
            ->columns([
                Tables\Columns\IconColumn::make('activity_type')
                    ->label('')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Activity')
                    ->formatStateUsing(fn ($record) => "New Order #{$record->invoice_no}")
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('game_info')
                    ->label('Details')
                    ->formatStateUsing(fn ($record) => 
                        ($record->game ? $record->game->name : 'Unknown Game') . " - " . 
                        ($record->user ? $record->user->name : 'Guest')
                    ),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => ['PAID', 'DELIVERED'],
                        'warning' => ['PENDING', 'UNPAID'],
                        'danger' => ['FAILED', 'EXPIRED'],
                    ]),
                    
                Tables\Columns\TextColumn::make('total')
                    ->label('Amount')
                    ->money('IDR'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('H:i')
                    ->description(fn ($record) => $record->created_at->diffForHumans())
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25])
            ->poll('30s');
    }
    
    protected function getLatestActivitiesQuery(): Builder
    {
        // Simply return recent orders for now
        return Order::query()
            ->with(['user', 'game'])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->take(20);
    }
}