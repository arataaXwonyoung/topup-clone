<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DenominationResource\Pages;
use App\Filament\Resources\DenominationResource\RelationManagers;
use App\Models\Denomination;
use App\Models\Game;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DenominationResource extends Resource
{
    protected static ?string $model = Denomination::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationGroup = 'Product Management';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $label = 'Denomination';
    
    protected static ?string $pluralLabel = 'Denominations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\Select::make('game_id')
                            ->label('Game')
                            ->relationship('game', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('publisher'),
                            ]),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(191)
                            ->placeholder('e.g., 86 Diamonds, 250 UC, 100 CP'),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount/Quantity')
                            ->required()
                            ->numeric()
                            ->placeholder('e.g., 86, 250, 100'),
                        
                        Forms\Components\TextInput::make('bonus')
                            ->label('Bonus Amount')
                            ->numeric()
                            ->default(0)
                            ->placeholder('Additional bonus amount')
                            ->helperText('Bonus items given with purchase'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Selling Price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('15000'),
                        
                        Forms\Components\TextInput::make('original_price')
                            ->label('Original Price (for discount display)')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('20000')
                            ->helperText('Show crossed-out price if higher than selling price'),
                        
                        Forms\Components\TextInput::make('cost_price')
                            ->label('Cost Price (Internal)')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('12000')
                            ->helperText('Your cost price from supplier'),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Digiflazz Integration')
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->label('Digiflazz SKU Code')
                            ->maxLength(191)
                            ->placeholder('ml86')
                            ->helperText('SKU code from Digiflazz for automatic fulfillment'),
                        
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Additional Data')
                            ->helperText('Extra data for integration (JSON format)'),
                    ])
                    ->columns(1),
                
                Forms\Components\Section::make('Display Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Available for purchase')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_hot')
                            ->label('Hot/Popular')
                            ->helperText('Mark as popular choice'),
                        
                        Forms\Components\Toggle::make('is_promo')
                            ->label('Promo/Special Offer')
                            ->helperText('Show as promotional item'),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])
                    ->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => 
                        $record->bonus > 0 
                            ? "{$record->amount} + {$record->bonus} bonus"
                            : $record->amount
                    ),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('original_price')
                    ->label('Original Price')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('No discount')
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('profit_margin')
                    ->label('Margin')
                    ->getStateUsing(fn ($record) => 
                        $record->cost_price && $record->cost_price > 0 
                            ? round((($record->price - $record->cost_price) / $record->price) * 100, 1) . '%'
                            : 'N/A'
                    )
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Sales')
                    ->counts(['orders' => fn (Builder $query) => $query->whereIn('status', ['PAID', 'DELIVERED'])])
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\IconColumn::make('is_hot')
                    ->label('Hot')
                    ->boolean()
                    ->trueIcon('heroicon-o-fire')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('danger')
                    ->falseColor('gray'),
                
                Tables\Columns\IconColumn::make('is_promo')
                    ->label('Promo')
                    ->boolean()
                    ->trueIcon('heroicon-o-tag')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->placeholder('No SKU')
                    ->fontFamily('mono'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('game_id')
                    ->label('Game')
                    ->relationship('game', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                
                Tables\Filters\TernaryFilter::make('is_hot')
                    ->label('Hot Items'),
                
                Tables\Filters\TernaryFilter::make('is_promo')
                    ->label('Promotional Items'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->emptyStateHeading('No denominations found')
            ->emptyStateDescription('Create your first denomination to start selling products.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDenominations::route('/'),
            'create' => Pages\CreateDenomination::route('/create'),
            'edit' => Pages\EditDenomination::route('/{record}/edit'),
        ];
    }
}