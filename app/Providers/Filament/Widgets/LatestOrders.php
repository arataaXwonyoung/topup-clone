<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Latest Orders';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->default('Guest'),
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => ['PAID', 'DELIVERED'],
                        'warning' => ['PENDING', 'UNPAID'],
                        'danger' => ['EXPIRED', 'FAILED'],
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Order $record): string => route('invoices.show', $record->invoice_no))
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated(false);
    }
}