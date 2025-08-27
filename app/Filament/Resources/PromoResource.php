<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoResource\Pages;
use App\Filament\Resources\PromoResource\RelationManagers;
use App\Models\Promo;
use App\Models\Game;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromoResource extends Resource
{
    protected static ?string $model = Promo::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    protected static ?string $navigationGroup = 'CMS';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Promo Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(191)
                            ->placeholder('e.g., New Year Discount, Flash Sale 50%'),
                        
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignorable: fn ($record) => $record)
                            ->maxLength(50)
                            ->placeholder('e.g., NEWYEAR2024, FLASH50')
                            ->helperText('Unique promo code that users will enter')
                            ->uppercase(),
                        
                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull()
                            ->placeholder('Description of the promo benefits and terms...'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Discount Settings')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Discount Type')
                            ->options([
                                'percent' => 'Percentage (%)',
                                'fixed' => 'Fixed Amount (Rp)',
                            ])
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('value')
                            ->label('Discount Value')
                            ->required()
                            ->numeric()
                            ->placeholder(fn (Forms\Get $get) => 
                                $get('type') === 'percent' ? '10 (for 10%)' : '50000 (for Rp 50,000)'
                            )
                            ->suffix(fn (Forms\Get $get) => 
                                $get('type') === 'percent' ? '%' : 'Rp'
                            ),
                        
                        Forms\Components\TextInput::make('min_total')
                            ->label('Minimum Purchase Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('100000')
                            ->helperText('Minimum order total to use this promo'),
                        
                        Forms\Components\TextInput::make('max_discount')
                            ->label('Maximum Discount Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('100000')
                            ->helperText('Maximum discount amount (useful for percentage discounts)')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'percent'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Usage Limits')
                    ->schema([
                        Forms\Components\TextInput::make('quota')
                            ->label('Total Usage Quota')
                            ->numeric()
                            ->placeholder('100')
                            ->helperText('Total number of times this promo can be used (leave empty for unlimited)'),
                        
                        Forms\Components\TextInput::make('per_user_limit')
                            ->label('Per User Limit')
                            ->numeric()
                            ->default(1)
                            ->helperText('Maximum times a single user can use this promo'),
                        
                        Forms\Components\Placeholder::make('used_count')
                            ->label('Times Used')
                            ->content(fn ($record) => $record?->used_count ?? 0),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Game Restrictions')
                    ->schema([
                        Forms\Components\CheckboxList::make('game_ids')
                            ->label('Applicable Games')
                            ->options(Game::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Leave empty to apply to all games')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Schedule & Status')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Start Date')
                            ->helperText('When the promo becomes active (leave empty for immediate)'),
                        
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('End Date')
                            ->helperText('When the promo expires (leave empty for no expiry)'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Enable/disable this promo')
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Promo Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => 
                        $state === 'percent' ? 'Percentage' : 'Fixed Amount'
                    )
                    ->color(fn (string $state): string => 
                        $state === 'percent' ? 'warning' : 'success'
                    ),
                
                Tables\Columns\TextColumn::make('value')
                    ->label('Discount')
                    ->formatStateUsing(fn ($record) => 
                        $record->type === 'percent' 
                            ? $record->value . '%'
                            : 'Rp ' . number_format($record->value, 0, ',', '.')
                    )
                    ->weight('bold')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('usage')
                    ->label('Usage')
                    ->getStateUsing(fn ($record) => 
                        $record->quota 
                            ? "{$record->used_count}/{$record->quota}"
                            : $record->used_count
                    )
                    ->badge()
                    ->color(fn ($record) => 
                        $record->quota && $record->used_count >= $record->quota ? 'danger' : 'info'
                    ),
                
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Start')
                    ->dateTime('M d, H:i')
                    ->sortable()
                    ->placeholder('Immediate')
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('End')
                    ->dateTime('M d, H:i')
                    ->sortable()
                    ->placeholder('No expiry')
                    ->color('warning'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percent' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                    ]),
                
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('ends_at', '<', now()))
                    ->label('Expired Promos'),
                
                Tables\Filters\Filter::make('quota_exceeded')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereColumn('used_count', '>=', 'quota')
                            ->whereNotNull('quota')
                    )
                    ->label('Quota Exceeded'),
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
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No promos found')
            ->emptyStateDescription('Create your first promo to offer discounts to customers.')
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
            'index' => Pages\ListPromos::route('/'),
            'create' => Pages\CreatePromo::route('/create'),
            'edit' => Pages\EditPromo::route('/{record}/edit'),
        ];
    }
}