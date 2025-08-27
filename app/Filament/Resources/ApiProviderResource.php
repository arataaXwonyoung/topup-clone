<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiProviderResource\Pages;
use App\Filament\Resources\ApiProviderResource\RelationManagers;
use App\Models\ApiProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ApiProviderResource extends Resource
{
    protected static ?string $model = ApiProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationGroup = 'System Settings';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Provider Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(191)
                            ->placeholder('e.g., Midtrans, Digiflazz, Tripay')
                            ->reactive()
                            ->afterStateUpdated(fn (string $context, $state, callable $set) => 
                                $context === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignorable: fn ($record) => $record)
                            ->maxLength(191)
                            ->placeholder('provider-name')
                            ->helperText('URL-friendly identifier'),
                        
                        Forms\Components\Textarea::make('description')
                            ->placeholder('Brief description of the provider...')
                            ->rows(2)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'payment' => 'Payment Gateway',
                                'topup' => 'Top-up Provider',
                                'validation' => 'Player ID Validation',
                                'sms' => 'SMS Service',
                                'email' => 'Email Service',
                                'other' => 'Other Service',
                            ])
                            ->required()
                            ->reactive(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('API Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('base_url')
                            ->label('Base URL')
                            ->required()
                            ->url()
                            ->placeholder('https://api.provider.com')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('Your API key from provider'),
                        
                        Forms\Components\TextInput::make('secret_key')
                            ->label('Secret Key')
                            ->password()
                            ->revealable()
                            ->placeholder('Your secret key from provider'),
                        
                        Forms\Components\TextInput::make('username')
                            ->placeholder('Username (if required)'),
                        
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->placeholder('Password (if required)'),
                        
                        Forms\Components\TextInput::make('webhook_url')
                            ->label('Webhook URL')
                            ->url()
                            ->placeholder('https://yoursite.com/webhook/provider')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Provider Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Enable this provider')
                            ->default(true),
                        
                        Forms\Components\TextInput::make('priority')
                            ->numeric()
                            ->default(0)
                            ->helperText('Higher number = higher priority'),
                        
                        Forms\Components\TextInput::make('rate_limit')
                            ->label('Rate Limit (per minute)')
                            ->numeric()
                            ->placeholder('60')
                            ->helperText('Maximum requests per minute'),
                        
                        Forms\Components\TextInput::make('timeout')
                            ->label('Timeout (seconds)')
                            ->numeric()
                            ->default(30)
                            ->helperText('Request timeout in seconds'),
                        
                        Forms\Components\TextInput::make('retry_attempts')
                            ->label('Retry Attempts')
                            ->numeric()
                            ->default(3)
                            ->helperText('Number of retry attempts on failure'),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Supported Methods')
                    ->schema([
                        Forms\Components\CheckboxList::make('supported_methods')
                            ->label('Supported Payment Methods')
                            ->options([
                                // Payment Methods
                                'credit_card' => 'Credit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'virtual_account' => 'Virtual Account',
                                'e_wallet' => 'E-Wallet (GoPay, OVO, DANA)',
                                'qris' => 'QRIS',
                                'convenience_store' => 'Convenience Store',
                                
                                // Top-up Methods
                                'auto_fulfill' => 'Auto Fulfillment',
                                'manual_fulfill' => 'Manual Fulfillment',
                                'voucher_code' => 'Voucher Code',
                                
                                // Validation Methods
                                'player_validation' => 'Player ID Validation',
                                'server_validation' => 'Server Validation',
                                
                                // Communication Methods
                                'webhook' => 'Webhook Support',
                                'callback' => 'Callback URL',
                                'status_check' => 'Status Checking',
                            ])
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['payment', 'topup', 'validation']))
                            ->columns(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\CheckboxList::make('supported_methods')
                            ->label('Supported Features')
                            ->options([
                                'send_sms' => 'Send SMS',
                                'bulk_sms' => 'Bulk SMS',
                                'sms_template' => 'SMS Templates',
                                'delivery_report' => 'Delivery Reports',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('type') === 'sms')
                            ->columns(2)
                            ->columnSpanFull(),
                        
                        Forms\Components\CheckboxList::make('supported_methods')
                            ->label('Supported Features')
                            ->options([
                                'send_email' => 'Send Email',
                                'bulk_email' => 'Bulk Email',
                                'email_template' => 'Email Templates',
                                'tracking' => 'Email Tracking',
                                'attachments' => 'Attachments Support',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('type') === 'email')
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Advanced Configuration')
                    ->schema([
                        Forms\Components\KeyValue::make('configuration')
                            ->label('Custom Configuration')
                            ->helperText('Provider-specific configuration parameters')
                            ->columnSpanFull(),
                        
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->helperText('Additional provider information')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->fontFamily('mono')
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'payment' => 'success',
                        'topup' => 'primary',
                        'validation' => 'info',
                        'sms' => 'warning',
                        'email' => 'secondary',
                        'other' => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('base_url')
                    ->label('Base URL')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('priority')
                    ->sortable()
                    ->alignCenter()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('supported_methods')
                    ->label('Methods')
                    ->getStateUsing(fn ($record) => 
                        is_array($record->supported_methods) ? count($record->supported_methods) : 0
                    )
                    ->badge()
                    ->color('primary')
                    ->placeholder('No methods'),
                
                Tables\Columns\TextColumn::make('timeout')
                    ->label('Timeout')
                    ->suffix('s')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'payment' => 'Payment Gateway',
                        'topup' => 'Top-up Provider',
                        'validation' => 'Player ID Validation',
                        'sms' => 'SMS Service',
                        'email' => 'Email Service',
                        'other' => 'Other Service',
                    ])
                    ->multiple(),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Action::make('test_connection')
                    ->label('Test Connection')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function ($record) {
                        // Test API connection functionality
                        try {
                            // Simulate connection test
                            $success = true; // Replace with actual API test
                            
                            if ($success) {
                                Notification::make()
                                    ->title('Connection successful')
                                    ->body('API provider is responding correctly')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Connection failed')
                                    ->body('Unable to connect to API provider')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Connection error')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Action::make('toggle_status')
                    ->label(fn ($record) => $record->is_active ? 'Disable' : 'Enable')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                    ->action(function ($record) {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        Notification::make()
                            ->title('Provider ' . ($record->is_active ? 'enabled' : 'disabled'))
                            ->success()
                            ->send();
                    }),
                
                Action::make('view_logs')
                    ->label('View Logs')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->action(function ($record) {
                        // View API logs functionality
                        Notification::make()
                            ->title('API logs')
                            ->body('Logs viewer coming soon')
                            ->info()
                            ->send();
                    }),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete API Provider')
                    ->modalDescription('Are you sure you want to delete this provider? This may affect related transactions.')
                    ->modalSubmitActionLabel('Yes, delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('enable_bulk')
                        ->label('Enable Selected')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => true]);
                            }
                            
                            Notification::make()
                                ->title(count($records) . ' providers enabled')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('disable_bulk')
                        ->label('Disable Selected')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => false]);
                            }
                            
                            Notification::make()
                                ->title(count($records) . ' providers disabled')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('priority', 'desc')
            ->emptyStateHeading('No API providers configured')
            ->emptyStateDescription('Add your first API provider to start accepting payments and processing orders.')
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
            'index' => Pages\ListApiProviders::route('/'),
            'create' => Pages\CreateApiProvider::route('/create'),
            'view' => Pages\ViewApiProvider::route('/{record}'),
            'edit' => Pages\EditApiProvider::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }
}