<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Game;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ReportResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationGroup = 'Reports & Analytics';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationLabel = 'Reports';
    
    protected static ?string $pluralModelLabel = 'Reports';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Configuration')
                    ->schema([
                        Forms\Components\Select::make('report_type')
                            ->label('Report Type')
                            ->options([
                                'sales' => 'Sales Report',
                                'revenue' => 'Revenue Report',
                                'customer' => 'Customer Report',
                                'product' => 'Product Performance',
                                'payment' => 'Payment Methods Report',
                                'monthly' => 'Monthly Summary',
                                'yearly' => 'Yearly Summary',
                                'custom' => 'Custom Report',
                            ])
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From Date')
                            ->required()
                            ->default(now()->subDays(30)),
                        
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To Date')
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Order::query())
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')),
                
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
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\DateRangeFilter::make('created_at')
                    ->label('Order Date Range'),
                
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
            ])
            ->actions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($record) {
                        // Export single record functionality
                        Notification::make()
                            ->title('Export started')
                            ->success()
                            ->send();
                    }),
                
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->action(function ($record) {
                        // Export PDF functionality
                        Notification::make()
                            ->title('PDF export started')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Action::make('generate_sales_report')
                    ->label('Sales Report')
                    ->icon('heroicon-o-chart-bar')
                    ->color('primary')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From Date')
                            ->required()
                            ->default(now()->subDays(30)),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To Date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('format')
                            ->options([
                                'excel' => 'Excel (XLSX)',
                                'csv' => 'CSV',
                                'pdf' => 'PDF',
                            ])
                            ->default('excel')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        // Generate comprehensive sales report
                        Notification::make()
                            ->title('Sales report generated')
                            ->body('Report will be available in downloads section')
                            ->success()
                            ->send();
                    }),
                
                Action::make('generate_revenue_report')
                    ->label('Revenue Report')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From Date')
                            ->required()
                            ->default(now()->subDays(30)),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To Date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('group_by')
                            ->options([
                                'day' => 'Daily',
                                'week' => 'Weekly',
                                'month' => 'Monthly',
                                'game' => 'By Game',
                                'payment_method' => 'By Payment Method',
                            ])
                            ->default('day')
                            ->required(),
                        Forms\Components\Select::make('format')
                            ->options([
                                'excel' => 'Excel (XLSX)',
                                'csv' => 'CSV',
                                'pdf' => 'PDF',
                            ])
                            ->default('excel')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        // Generate revenue report
                        Notification::make()
                            ->title('Revenue report generated')
                            ->success()
                            ->send();
                    }),
                
                Action::make('generate_customer_report')
                    ->label('Customer Report')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From Date')
                            ->required()
                            ->default(now()->subDays(30)),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To Date')
                            ->required()
                            ->default(now()),
                        Forms\Components\CheckboxList::make('include_fields')
                            ->label('Include Fields')
                            ->options([
                                'orders_count' => 'Total Orders',
                                'total_spent' => 'Total Spent',
                                'last_order' => 'Last Order Date',
                                'favorite_games' => 'Favorite Games',
                                'payment_methods' => 'Payment Methods Used',
                            ])
                            ->default(['orders_count', 'total_spent'])
                            ->columns(2),
                        Forms\Components\Select::make('format')
                            ->options([
                                'excel' => 'Excel (XLSX)',
                                'csv' => 'CSV',
                            ])
                            ->default('excel')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        // Generate customer report
                        Notification::make()
                            ->title('Customer report generated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('format')
                                ->options([
                                    'excel' => 'Excel (XLSX)',
                                    'csv' => 'CSV',
                                    'pdf' => 'PDF',
                                ])
                                ->default('excel')
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            Notification::make()
                                ->title('Export of ' . count($records) . ' records started')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'analytics' => Pages\AnalyticsReport::route('/analytics'),
            'revenue' => Pages\RevenueReport::route('/revenue'),
            'customers' => Pages\CustomerReport::route('/customers'),
            'products' => Pages\ProductReport::route('/products'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return null;
    }
}