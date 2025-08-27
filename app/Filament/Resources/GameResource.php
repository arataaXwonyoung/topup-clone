<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Filament\Resources\GameResource\RelationManagers;
use App\Models\Game;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    
    protected static ?string $navigationGroup = 'Product Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(191)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) => 
                                $context === 'create' ? $set('slug', Str::slug($state)) : null),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(191)
                            ->unique(ignorable: fn ($record) => $record)
                            ->helperText('Auto-generated from name, but can be customized'),
                        
                        Forms\Components\TextInput::make('publisher')
                            ->maxLength(191)
                            ->placeholder('e.g., Moonton, Tencent, PUBG Corporation'),
                        
                        Forms\Components\Select::make('category')
                            ->options([
                                'moba' => 'MOBA',
                                'battle_royale' => 'Battle Royale',
                                'mmorpg' => 'MMORPG',
                                'fps' => 'FPS',
                                'strategy' => 'Strategy',
                                'card' => 'Card Game',
                                'casual' => 'Casual',
                                'voucher' => 'Voucher',
                                'other' => 'Other',
                            ])
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\FileUpload::make('cover_path')
                            ->label('Game Cover')
                            ->image()
                            ->directory('games')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('450'),
                        
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Brief description of the game...'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Player ID Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('digiflazz_code')
                            ->label('Digiflazz Product Code')
                            ->maxLength(191)
                            ->helperText('Product code from Digiflazz API for validation'),
                        
                        Forms\Components\Toggle::make('enable_validation')
                            ->label('Enable Player ID Validation')
                            ->helperText('Validate Player ID through Digiflazz API')
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('id_label')
                            ->label('Player ID Label')
                            ->required()
                            ->maxLength(191)
                            ->default('User ID')
                            ->placeholder('e.g., User ID, Mobile Legends ID, PUBG ID'),
                        
                        Forms\Components\Toggle::make('requires_server')
                            ->label('Requires Server/Zone Selection')
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('server_label')
                            ->label('Server/Zone Label')
                            ->required()
                            ->maxLength(191)
                            ->default('Server')
                            ->placeholder('e.g., Server, Zone, Region')
                            ->visible(fn (Forms\Get $get) => $get('requires_server')),
                        
                        Forms\Components\RichEditor::make('validation_instructions')
                            ->label('Validation Instructions')
                            ->columnSpanFull()
                            ->placeholder('Instructions for users on how to find their Player ID...')
                            ->visible(fn (Forms\Get $get) => $get('enable_validation')),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Display Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Show this game on the website')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_hot')
                            ->label('Hot/Featured')
                            ->helperText('Mark as hot/trending game'),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_path')
                    ->label('Cover')
                    ->circular()
                    ->size(60),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Game Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('publisher')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No publisher'),
                
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'moba' => 'info',
                        'battle_royale' => 'success',
                        'mmorpg' => 'warning',
                        'fps' => 'danger',
                        'strategy' => 'primary',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('denominations_count')
                    ->label('Items')
                    ->counts('denominations')
                    ->suffix(' items')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts(['orders' => fn (Builder $query) => $query->whereIn('status', ['PAID', 'DELIVERED'])])
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\IconColumn::make('enable_validation')
                    ->label('Validation')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                Tables\Columns\IconColumn::make('is_hot')
                    ->label('Hot')
                    ->boolean()
                    ->trueIcon('heroicon-o-fire')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('danger')
                    ->falseColor('gray'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                
                Tables\Filters\TernaryFilter::make('is_hot')
                    ->label('Hot/Featured'),
                
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'moba' => 'MOBA',
                        'battle_royale' => 'Battle Royale',
                        'mmorpg' => 'MMORPG',
                        'fps' => 'FPS',
                        'strategy' => 'Strategy',
                        'card' => 'Card Game',
                        'casual' => 'Casual',
                        'voucher' => 'Voucher',
                        'other' => 'Other',
                    ]),
                
                Tables\Filters\TernaryFilter::make('enable_validation')
                    ->label('Has Validation'),
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
            ->emptyStateHeading('No games found')
            ->emptyStateDescription('Create your first game to start selling top-up products.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\DenominationsRelationManager::class,
            // RelationManagers\OrdersRelationManager::class,
            // RelationManagers\ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
        ];
    }
}