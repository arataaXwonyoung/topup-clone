<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationLabel = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setting Details')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(fn ($record) => $record && in_array($record->key, [
                                'site_name', 'site_url', 'admin_email', 'default_currency'
                            ]))
                            ->helperText('System keys cannot be modified'),
                            
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Display name for this setting'),
                            
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Brief description of what this setting controls'),
                            
                        Forms\Components\Select::make('type')
                            ->options([
                                'text' => 'Text',
                                'textarea' => 'Long Text',
                                'number' => 'Number',
                                'boolean' => 'Yes/No',
                                'select' => 'Select Option',
                                'file' => 'File Upload',
                                'json' => 'JSON Data',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('value', null)),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Setting Value')
                    ->schema([
                        // Text Input
                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'number']))
                            ->numeric(fn (Forms\Get $get) => $get('type') === 'number'),
                            
                        // Textarea
                        Forms\Components\Textarea::make('value')
                            ->label('Value')
                            ->rows(4)
                            ->visible(fn (Forms\Get $get) => $get('type') === 'textarea'),
                            
                        // Boolean Toggle
                        Forms\Components\Toggle::make('boolean_value')
                            ->label('Enabled')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'boolean')
                            ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('value', $state ? '1' : '0')),
                            
                        // Select Options
                        Forms\Components\KeyValue::make('options')
                            ->label('Available Options')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'select')
                            ->helperText('Key-value pairs for select options'),
                            
                        Forms\Components\Select::make('value')
                            ->label('Selected Value')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'select')
                            ->options(fn (Forms\Get $get) => $get('options') ?? []),
                            
                        // File Upload
                        Forms\Components\FileUpload::make('value')
                            ->label('File')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'file')
                            ->directory('settings')
                            ->maxSize(5120),
                            
                        // JSON Data
                        Forms\Components\Textarea::make('value')
                            ->label('JSON Value')
                            ->rows(6)
                            ->visible(fn (Forms\Get $get) => $get('type') === 'json')
                            ->helperText('Valid JSON format required'),
                    ])
                    ->columnSpanFull(),
                    
                Forms\Components\Section::make('Organization')
                    ->schema([
                        Forms\Components\Select::make('group')
                            ->options([
                                'general' => 'General',
                                'payment' => 'Payment',
                                'email' => 'Email',
                                'social' => 'Social Media',
                                'seo' => 'SEO',
                                'security' => 'Security',
                                'api' => 'API',
                                'appearance' => 'Appearance',
                            ])
                            ->default('general')
                            ->required(),
                            
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Order within group (lower numbers first)'),
                            
                        Forms\Components\Toggle::make('is_public')
                            ->label('Public')
                            ->helperText('Can be accessed by frontend')
                            ->default(false),
                            
                        Forms\Components\Toggle::make('is_encrypted')
                            ->label('Encrypted')
                            ->helperText('Store value encrypted (for sensitive data)')
                            ->default(false),
                    ])
                    ->columns(2),
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
                    
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'primary',
                        'payment' => 'success',
                        'email' => 'warning',
                        'security' => 'danger',
                        'api' => 'info',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'boolean' => 'success',
                        'number' => 'warning',
                        'select' => 'info',
                        'file' => 'danger',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('display_value')
                    ->label('Current Value')
                    ->state(function ($record) {
                        if ($record->type === 'boolean') {
                            return $record->value ? 'Yes' : 'No';
                        }
                        if ($record->type === 'file' && $record->value) {
                            return 'Uploaded (' . basename($record->value) . ')';
                        }
                        if ($record->is_encrypted) {
                            return '••••••••';
                        }
                        if (strlen($record->value) > 50) {
                            return substr($record->value, 0, 50) . '...';
                        }
                        return $record->value ?: '—';
                    })
                    ->limit(50),
                    
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),
                    
                Tables\Columns\IconColumn::make('is_encrypted')
                    ->label('Encrypted')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'payment' => 'Payment',
                        'email' => 'Email',
                        'social' => 'Social Media',
                        'seo' => 'SEO',
                        'security' => 'Security',
                        'api' => 'API',
                        'appearance' => 'Appearance',
                    ]),
                    
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'textarea' => 'Long Text',
                        'number' => 'Number',
                        'boolean' => 'Yes/No',
                        'select' => 'Select Option',
                        'file' => 'File Upload',
                        'json' => 'JSON Data',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public'),
                    
                Tables\Filters\TernaryFilter::make('is_encrypted')
                    ->label('Encrypted'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reset')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => !in_array($record->key, [
                        'site_name', 'site_url', 'admin_email', 'default_currency'
                    ]))
                    ->action(fn ($record) => $record->update(['value' => $record->default_value])),
                    
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => !in_array($record->key, [
                        'site_name', 'site_url', 'admin_email', 'default_currency'
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_config')
                        ->label('Export Configuration')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            $config = $records->mapWithKeys(fn ($record) => [
                                $record->key => [
                                    'name' => $record->name,
                                    'value' => $record->value,
                                    'type' => $record->type,
                                    'group' => $record->group,
                                ]
                            ]);
                            
                            $filename = 'settings_export_' . now()->format('Y_m_d_H_i_s') . '.json';
                            
                            return response()->streamDownload(
                                fn () => print json_encode($config, JSON_PRETTY_PRINT),
                                $filename,
                                ['Content-Type' => 'application/json']
                            );
                        }),
                ]),
            ])
            ->defaultSort('group');
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        // Show count of settings that need attention (missing values, etc)
        $count = static::getModel()::whereNull('value')
            ->orWhere('value', '')
            ->count();
            
        return $count > 0 ? (string) $count : null;
    }
}