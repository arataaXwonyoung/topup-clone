<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrdersWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user', 'game', 'denomination', 'payment'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
                
                Tables\Columns\TextColumn::make('denomination.name')
                    ->label('Item')
                    ->limit(20),
                
                Tables\Columns\TextColumn::make('total')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'PAID' => 'info',
                        'DELIVERED' => 'success',
                        'FAILED' => 'danger',
                        'CANCELLED' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'PENDING' => 'heroicon-o-clock',
                        'PAID' => 'heroicon-o-credit-card',
                        'DELIVERED' => 'heroicon-o-check-circle',
                        'FAILED' => 'heroicon-o-x-circle',
                        'CANCELLED' => 'heroicon-o-ban',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                
                Tables\Columns\TextColumn::make('payment.method')
                    ->label('Payment')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('d M Y H:i:s')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', $record)),
            ])
            ->heading('Latest Transactions')
            ->description('Recent orders and their status')
            ->emptyStateHeading('No recent transactions')
            ->emptyStateDescription('Transactions will appear here as they are created.')
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}