<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersTable extends BaseWidget
{
    protected static ?string $heading = 'Order Terakhir';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->with(['user', 'game', 'payment'])->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable()
                    ->copyable()
                    ->tooltip('Click to copy'),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->default('Guest')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game')
                    ->limit(20),
                    
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('payment.method')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'QRIS' => 'warning',
                        'VA' => 'info',
                        'EWALLET' => 'success',
                        default => 'gray',
                    }),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => ['PAID', 'DELIVERED'],
                        'warning' => ['PENDING', 'UNPAID'],
                        'danger' => ['EXPIRED', 'FAILED'],
                    ]),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', $record))
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated(false);
    }
}