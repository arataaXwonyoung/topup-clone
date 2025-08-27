<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\User;
use App\Models\Game;
use App\Models\Denomination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    protected static ?string $navigationGroup = 'Order Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_no')
                            ->label('Invoice Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        
                        Forms\Components\Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('email')->email()->required(),
                                Forms\Components\TextInput::make('password')->password()->required(),
                            ]),
                        
                        Forms\Components\Select::make('game_id')
                            ->label('Game')
                            ->relationship('game', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('denomination_id', null)),
                        
                        Forms\Components\Select::make('denomination_id')
                            ->label('Product')
                            ->options(function (callable $get) {
                                $gameId = $get('game_id');
                                if (!$gameId) return [];
                                
                                return Denomination::where('game_id', $gameId)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->reactive(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Customer Details')
                    ->schema([
                        Forms\Components\TextInput::make('player_id')
                            ->label('Player ID')
                            ->required()
                            ->placeholder('Enter player ID/username'),
                        
                        Forms\Components\TextInput::make('server_id')
                            ->label('Server ID')
                            ->placeholder('Server/Zone ID (if required)'),
                        
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Customer Name')
                            ->placeholder('Customer display name'),
                        
                        Forms\Components\TextInput::make('customer_email')
                            ->label('Customer Email')
                            ->email()
                            ->placeholder('customer@example.com'),
                        
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Customer Phone')
                            ->tel()
                            ->placeholder('+62812345678'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->minValue(1)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $denominationId = $get('denomination_id');
                                if ($denominationId) {
                                    $denomination = Denomination::find($denominationId);
                                    if ($denomination) {
                                        $subtotal = $denomination->price * $state;
                                        $set('subtotal', $subtotal);
                                        $set('total', $subtotal);
                                    }
                                }
                            }),
                        
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),
                        
                        Forms\Components\TextInput::make('discount_amount')
                            ->label('Discount')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $subtotal = $get('subtotal') ?? 0;
                                $fees = $get('admin_fee') ?? 0;
                                $total = $subtotal - $state + $fees;
                                $set('total', max(0, $total));
                            }),
                        
                        Forms\Components\TextInput::make('admin_fee')
                            ->label('Admin Fee')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $subtotal = $get('subtotal') ?? 0;
                                $discount = $get('discount_amount') ?? 0;
                                $total = $subtotal - $discount + $state;
                                $set('total', max(0, $total));
                            }),
                        
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Status & Payment')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'PENDING' => 'Pending Payment',
                                'PAID' => 'Paid',
                                'PROCESSING' => 'Processing',
                                'DELIVERED' => 'Delivered',
                                'FAILED' => 'Failed',
                                'EXPIRED' => 'Expired',
                                'CANCELLED' => 'Cancelled',
                            ])
                            ->required()
                            ->default('PENDING'),
                        
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'midtrans' => 'Midtrans',
                                'xendit' => 'Xendit',
                                'tripay' => 'Tripay',
                                'manual' => 'Manual',
                                'free' => 'Free',
                            ])
                            ->placeholder('Select payment method'),
                        
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Payment Expires At')
                            ->helperText('Payment deadline'),
                        
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->helperText('Payment completion time'),
                        
                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Delivered At')
                            ->helperText('Order fulfillment time'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\TextInput::make('promo_code')
                            ->label('Promo Code')
                            ->placeholder('Applied promo code'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Order Notes')
                            ->placeholder('Special instructions or notes...')
                            ->rows(3),
                        
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->helperText('Additional order data (JSON format)'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('denomination.name')
                    ->label('Product')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                
                Tables\Columns\TextColumn::make('player_id')
                    ->label('Player ID')
                    ->searchable()
                    ->fontFamily('mono')
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'PAID' => 'info',
                        'PROCESSING' => 'primary',
                        'DELIVERED' => 'success',
                        'FAILED' => 'danger',
                        'EXPIRED' => 'gray',
                        'CANCELLED' => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->color('info')
                    ->placeholder('No method'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('M d, H:i')
                    ->sortable()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M d, H:i')
                    ->placeholder('No expiry')
                    ->color(fn ($record) => 
                        $record?->expires_at && $record->expires_at < now() ? 'danger' : 'warning'
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'Pending Payment',
                        'PAID' => 'Paid',
                        'PROCESSING' => 'Processing',
                        'DELIVERED' => 'Delivered',
                        'FAILED' => 'Failed',
                        'EXPIRED' => 'Expired',
                        'CANCELLED' => 'Cancelled',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'midtrans' => 'Midtrans',
                        'xendit' => 'Xendit',
                        'tripay' => 'Tripay',
                        'manual' => 'Manual',
                        'free' => 'Free',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('game_id')
                    ->label('Game')
                    ->relationship('game', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Customer')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
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
                
                Tables\Filters\Filter::make('high_value')
                    ->query(fn (Builder $query): Builder => $query->where('total', '>=', 100000))
                    ->label('High Value Orders (≥100K)'),
                
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('expires_at', '<', now())->where('status', 'PENDING')
                    )
                    ->label('Expired Orders'),
            ])
            ->actions([
                Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'PENDING')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'PAID',
                            'paid_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Order marked as paid')
                            ->success()
                            ->send();
                    }),
                
                Action::make('mark_processing')
                    ->label('Processing')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->visible(fn ($record) => $record->status === 'PAID')
                    ->action(function ($record) {
                        $record->update(['status' => 'PROCESSING']);
                        
                        Notification::make()
                            ->title('Order marked as processing')
                            ->success()
                            ->send();
                    }),
                
                Action::make('mark_delivered')
                    ->label('Mark Delivered')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['PAID', 'PROCESSING']))
                    ->form([
                        Forms\Components\Textarea::make('delivery_notes')
                            ->label('Delivery Notes')
                            ->placeholder('Order delivered successfully...'),
                        Forms\Components\Toggle::make('notify_customer')
                            ->label('Notify Customer')
                            ->default(true),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'DELIVERED',
                            'delivered_at' => now(),
                            'notes' => $data['delivery_notes'] ? 
                                ($record->notes ? $record->notes . "\n\n" . $data['delivery_notes'] : $data['delivery_notes']) 
                                : $record->notes,
                        ]);
                        
                        if ($data['notify_customer']) {
                            // Send notification to customer
                        }
                        
                        Notification::make()
                            ->title('Order marked as delivered')
                            ->success()
                            ->send();
                    }),
                
                Action::make('cancel_order')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['PENDING', 'PAID']))
                    ->form([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Cancellation Reason')
                            ->required()
                            ->placeholder('Reason for cancellation...'),
                        Forms\Components\Toggle::make('refund_payment')
                            ->label('Process Refund')
                            ->helperText('Refund payment to customer')
                            ->visible(fn ($record) => $record->status === 'PAID'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'CANCELLED',
                            'notes' => $data['cancellation_reason'],
                        ]);
                        
                        if ($data['refund_payment'] ?? false) {
                            // Process refund logic here
                        }
                        
                        Notification::make()
                            ->title('Order cancelled successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('resend_notification')
                    ->label('Resend Notification')
                    ->icon('heroicon-o-bell')
                    ->color('info')
                    ->action(function ($record) {
                        // Resend order notification logic
                        Notification::make()
                            ->title('Notification sent to customer')
                            ->success()
                            ->send();
                    }),
                
                Action::make('view_payment')
                    ->label('Payment Details')
                    ->icon('heroicon-o-credit-card')
                    ->color('gray')
                    ->url(fn ($record) => $record->payments()->exists() ? 
                        '/admin/payments?tableFilters[order_id][value]=' . $record->id : null)
                    ->visible(fn ($record) => $record->payments()->exists())
                    ->openUrlInNewTab(),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Order')
                    ->modalDescription('Are you sure? This will also delete related payment records.')
                    ->visible(fn ($record) => in_array($record->status, ['CANCELLED', 'EXPIRED', 'FAILED'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('mark_delivered_bulk')
                        ->label('Mark as Delivered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (in_array($record->status, ['PAID', 'PROCESSING'])) {
                                    $record->update([
                                        'status' => 'DELIVERED',
                                        'delivered_at' => now(),
                                    ]);
                                }
                            }
                            
                            Notification::make()
                                ->title(count($records) . ' orders marked as delivered')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('export_orders')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($records) {
                            // Export functionality would go here
                            Notification::make()
                                ->title('Export started')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No orders found')
            ->emptyStateDescription('Orders will appear here as customers place them.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
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
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'PENDING')->count();
    }
    
    public static function getWidgets(): array
    {
        return [
            // OrderStatsOverviewWidget::class,
        ];
    }
}