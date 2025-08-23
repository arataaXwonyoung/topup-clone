<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?string $heading = 'Latest Orders';
    protected static ?int $sort = 5;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game'),
                Tables\Columns\TextColumn::make('total')
                    ->money('IDR'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => ['DELIVERED'],
                        'info' => ['PAID'],
                        'warning' => ['PENDING', 'UNPAID'],
                        'danger' => ['FAILED', 'EXPIRED'],
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M H:i')
                    ->sortable(),
            ])
            ->paginated(false)
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}