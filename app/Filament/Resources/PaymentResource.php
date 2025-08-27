<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationGroup = 'Order Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\TextInput::make('payment_id')
                            ->label('Payment ID')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        
                        Forms\Components\Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'invoice_no')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('invoice_no')->required(),
                            ]),
                        
                        Forms\Components\Select::make('method')
                            ->label('Payment Method')
                            ->options([
                                'midtrans' => 'Midtrans',
                                'xendit' => 'Xendit',
                                'tripay' => 'Tripay',
                                'manual' => 'Manual Transfer',
                                'free' => 'Free/Promo',
                                'balance' => 'Account Balance',
                            ])
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\Select::make('channel')
                            ->label('Payment Channel')
                            ->options([
                                // Midtrans
                                'credit_card' => 'Credit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'bca_va' => 'BCA Virtual Account',
                                'bni_va' => 'BNI Virtual Account',
                                'bri_va' => 'BRI Virtual Account',
                                'mandiri_va' => 'Mandiri Virtual Account',
                                'permata_va' => 'Permata Virtual Account',
                                'other_va' => 'Other Virtual Account',
                                'gopay' => 'GoPay',
                                'shopeepay' => 'ShopeePay',
                                'qris' => 'QRIS',
                                'alfamart' => 'Alfamart',
                                'indomaret' => 'Indomaret',
                                
                                // Xendit
                                'ovo' => 'OVO',
                                'dana' => 'DANA',
                                'linkaja' => 'LinkAja',
                                
                                // Manual
                                'manual_bca' => 'Manual BCA',
                                'manual_bni' => 'Manual BNI',
                                'manual_bri' => 'Manual BRI',
                                'manual_mandiri' => 'Manual Mandiri',
                            ])
                            ->placeholder('Select payment channel'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Payment Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        
                        Forms\Components\TextInput::make('fee')
                            ->label('Payment Fee')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->helperText('Fee charged by payment gateway'),
                        
                        Forms\Components\TextInput::make('net_amount')
                            ->label('Net Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Amount after deducting fees'),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                                'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->default('pending'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Gateway Information')
                    ->schema([
                        Forms\Components\TextInput::make('gateway_transaction_id')
                            ->label('Gateway Transaction ID')
                            ->placeholder('Transaction ID from payment gateway'),
                        
                        Forms\Components\TextInput::make('gateway_reference')
                            ->label('Gateway Reference')
                            ->placeholder('Reference number from gateway'),
                        
                        Forms\Components\TextInput::make('virtual_account')
                            ->label('Virtual Account Number')
                            ->placeholder('VA number for bank transfers'),
                        
                        Forms\Components\TextInput::make('payment_code')
                            ->label('Payment Code')
                            ->placeholder('Payment code for convenience stores'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->helperText('When payment was completed'),
                        
                        Forms\Components\DateTimePicker::make('expired_at')
                            ->label('Expires At')
                            ->helperText('Payment expiry time'),
                        
                        Forms\Components\DateTimePicker::make('refunded_at')
                            ->label('Refunded At')
                            ->helperText('When refund was processed'),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('gateway_response')
                            ->label('Gateway Response')
                            ->placeholder('Raw response from payment gateway...')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Payment Notes')
                            ->placeholder('Additional notes about this payment...')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->helperText('Additional payment data (JSON format)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_id')
                    ->label('Payment ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('order.invoice_no')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->url(fn ($record) => '/admin/orders/' . $record->order_id)
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('order.user.name')
                    ->label('Customer')
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('channel')
                    ->label('Channel')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Not specified'),
                
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('fee')
                    ->label('Fee')
                    ->money('IDR')
                    ->placeholder('No fee')
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('net_amount')
                    ->label('Net')
                    ->money('IDR')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'expired' => 'gray',
                        'cancelled' => 'gray',
                        'refunded' => 'info',
                    }),
                
                Tables\Columns\TextColumn::make('gateway_transaction_id')
                    ->label('Gateway ID')
                    ->limit(20)
                    ->fontFamily('mono')
                    ->copyable()
                    ->placeholder('No ID'),
                
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('M d, H:i')
                    ->placeholder('Not paid')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, H:i')
                    ->sortable()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('method')
                    ->options([
                        'midtrans' => 'Midtrans',
                        'xendit' => 'Xendit',
                        'tripay' => 'Tripay',
                        'manual' => 'Manual Transfer',
                        'free' => 'Free/Promo',
                        'balance' => 'Account Balance',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('channel')
                    ->options([
                        'credit_card' => 'Credit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'bca_va' => 'BCA VA',
                        'bni_va' => 'BNI VA',
                        'bri_va' => 'BRI VA',
                        'mandiri_va' => 'Mandiri VA',
                        'gopay' => 'GoPay',
                        'ovo' => 'OVO',
                        'dana' => 'DANA',
                        'shopeepay' => 'ShopeePay',
                        'qris' => 'QRIS',
                    ])
                    ->multiple(),
                
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
                    ->query(fn (Builder $query): Builder => $query->where('amount', '>=', 500000))
                    ->label('High Value (≥500K)'),
                
                Tables\Filters\Filter::make('failed_payments')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', ['failed', 'expired', 'cancelled']))
                    ->label('Failed Payments'),
            ])
            ->actions([
                Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\TextInput::make('gateway_transaction_id')
                            ->label('Gateway Transaction ID')
                            ->placeholder('Transaction ID from gateway'),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Payment Time')
                            ->default(now()),
                        Forms\Components\Textarea::make('notes')
                            ->label('Payment Notes')
                            ->placeholder('Manual payment confirmation notes...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'paid',
                            'paid_at' => $data['paid_at'] ?? now(),
                            'gateway_transaction_id' => $data['gateway_transaction_id'],
                            'notes' => $data['notes'],
                        ]);
                        
                        // Update order status
                        $record->order->update(['status' => 'PAID', 'paid_at' => $data['paid_at'] ?? now()]);
                        
                        Notification::make()
                            ->title('Payment marked as paid')
                            ->success()
                            ->send();
                    }),
                
                Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'paid')
                    ->form([
                        Forms\Components\TextInput::make('refund_amount')
                            ->label('Refund Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(fn ($record) => $record->amount)
                            ->required(),
                        Forms\Components\Textarea::make('refund_reason')
                            ->label('Refund Reason')
                            ->required()
                            ->placeholder('Reason for refund...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'refunded',
                            'refunded_at' => now(),
                            'notes' => $data['refund_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Payment marked as refunded')
                            ->success()
                            ->send();
                    }),
                
                Action::make('view_order')
                    ->label('View Order')
                    ->icon('heroicon-o-shopping-bag')
                    ->url(fn ($record) => '/admin/orders/' . $record->order_id)
                    ->openUrlInNewTab(),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['cancelled', 'failed', 'expired'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('mark_paid_bulk')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'paid',
                                        'paid_at' => now(),
                                    ]);
                                    $record->order->update(['status' => 'PAID', 'paid_at' => now()]);
                                }
                            }
                            
                            Notification::make()
                                ->title(count($records) . ' payments marked as paid')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('export_payments')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($records) {
                            Notification::make()
                                ->title('Export started')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No payments found')
            ->emptyStateDescription('Payment records will appear here as orders are processed.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }
}