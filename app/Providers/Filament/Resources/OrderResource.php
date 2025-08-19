<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Game;
use App\Models\Denomination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?int $navigationSort = 1;
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['PENDING', 'UNPAID'])->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() > 0 ? 'warning' : 'gray';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_no')
                            ->label('Invoice Number')
                            ->disabled()
                            ->columnSpan(2),
                            
                        Forms\Components\Select::make('status')
                            ->options([
                                'PENDING' => 'Pending',
                                'UNPAID' => 'Unpaid',
                                'PAID' => 'Paid',
                                'DELIVERED' => 'Delivered',
                                'EXPIRED' => 'Expired',
                                'FAILED' => 'Failed',
                                'REFUNDED' => 'Refunded',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                            
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                    ])
                    ->columns(4),
                    
                Forms\Components\Section::make('Game & Item Details')
                    ->schema([
                        Forms\Components\Select::make('game_id')
                            ->relationship('game', 'name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('denomination_id', null)),
                            
                        Forms\Components\Select::make('denomination_id')
                            ->label('Item/Denomination')
                            ->options(function (callable $get) {
                                $gameId = $get('game_id');
                                if (!$gameId) {
                                    return [];
                                }
                                return Denomination::where('game_id', $gameId)
                                    ->pluck('name', 'id');
                            })
                            ->required(),
                            
                        Forms\Components\TextInput::make('account_id')
                            ->label('Game Account ID')
                            ->required(),
                            
                        Forms\Components\TextInput::make('server_id')
                            ->label('Server ID'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Contact & Payment')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required(),
                            
                        Forms\Components\TextInput::make('whatsapp')
                            ->tel()
                            ->required(),
                            
                        Forms\Components\TextInput::make('promo_code')
                            ->label('Promo Code Used'),
                            
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Financial Details')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('discount')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('fee')
                            ->label('Admin Fee')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('total')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->default('Guest')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->game?->name),
                    
                Tables\Columns\TextColumn::make('denomination.name')
                    ->label('Item')
                    ->limit(20),
                    
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => ['PAID', 'DELIVERED'],
                        'warning' => ['PENDING', 'UNPAID'],
                        'danger' => ['EXPIRED', 'FAILED', 'REFUNDED'],
                    ]),
                    
                Tables\Columns\TextColumn::make('payment.method')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'QRIS' => 'warning',
                        'VA' => 'info',
                        'EWALLET' => 'success',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'PENDING' => 'Pending',
                        'UNPAID' => 'Unpaid',
                        'PAID' => 'Paid',
                        'DELIVERED' => 'Delivered',
                        'EXPIRED' => 'Expired',
                        'FAILED' => 'Failed',
                        'REFUNDED' => 'Refunded',
                    ]),
                    
                SelectFilter::make('game_id')
                    ->label('Game')
                    ->relationship('game', 'name')
                    ->searchable()
                    ->preload(),
                    
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => in_array($record->status, ['PENDING', 'UNPAID']))
                    ->action(fn (Order $record) => $record->update(['status' => 'PAID'])),
                    
                Tables\Actions\Action::make('deliver')
                    ->label('Mark Delivered')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'PAID')
                    ->action(fn (Order $record) => $record->markAsDelivered()),
                    
                Tables\Actions\Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => in_array($record->status, ['PAID', 'DELIVERED']))
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Refund Reason')
                            ->required(),
                    ])
                    ->action(fn (Order $record, array $data) => $record->update([
                        'status' => 'REFUNDED',
                        'metadata' => array_merge($record->metadata ?? [], ['refund_reason' => $data['reason']])
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn ($records) => static::exportOrders($records)),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Order Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('invoice_no')
                            ->label('Invoice Number')
                            ->copyable(),
                            
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'PAID', 'DELIVERED' => 'success',
                                'PENDING', 'UNPAID' => 'warning',
                                'REFUNDED' => 'info',
                                default => 'danger',
                            }),
                            
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Order Date')
                            ->dateTime('d M Y H:i:s'),
                            
                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('Paid At')
                            ->dateTime('d M Y H:i:s')
                            ->default('-'),
                    ])
                    ->columns(2),
                    
                Infolists\Components\Section::make('Customer Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Customer Name')
                            ->default('Guest'),
                            
                        Infolists\Components\TextEntry::make('email')
                            ->copyable(),
                            
                        Infolists\Components\TextEntry::make('whatsapp')
                            ->label('WhatsApp')
                            ->copyable(),
                            
                        Infolists\Components\TextEntry::make('account_id')
                            ->label('Game Account ID'),
                    ])
                    ->columns(2),
                    
                Infolists\Components\Section::make('Product Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('game.name')
                            ->label('Game'),
                            
                        Infolists\Components\TextEntry::make('denomination.name')
                            ->label('Item'),
                            
                        Infolists\Components\TextEntry::make('quantity')
                            ->label('Quantity'),
                            
                        Infolists\Components\TextEntry::make('promo_code')
                            ->label('Promo Code')
                            ->default('-'),
                    ])
                    ->columns(2),
                    
                Infolists\Components\Section::make('Payment Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money('IDR'),
                            
                        Infolists\Components\TextEntry::make('discount')
                            ->label('Discount')
                            ->money('IDR'),
                            
                        Infolists\Components\TextEntry::make('fee')
                            ->label('Admin Fee')
                            ->money('IDR'),
                            
                        Infolists\Components\TextEntry::make('total')
                            ->label('Total')
                            ->money('IDR')
                            ->weight('bold'),
                            
                        Infolists\Components\TextEntry::make('payment.method')
                            ->label('Payment Method')
                            ->badge(),
                            
                        Infolists\Components\TextEntry::make('payment.status')
                            ->label('Payment Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'PAID' => 'success',
                                'PENDING' => 'warning',
                                default => 'danger',
                            }),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'game', 'denomination', 'payment']);
    }
    
    protected static function exportOrders($records)
    {
        // Implementation for export functionality
        // You can use Laravel Excel or generate CSV
    }
}