<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    
    protected static ?string $title = 'Order History';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_no')
                    ->disabled()
                    ->label('Invoice'),
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
                    ->required(),
                Forms\Components\TextInput::make('total')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_no')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),
                
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game')
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('denomination.name')
                    ->label('Product')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('player_id')
                    ->label('Player ID')
                    ->searchable()
                    ->fontFamily('mono'),
                
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
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('M d, H:i')
                    ->sortable(),
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
                    ]),
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
            ])
            ->headerActions([
                // No create action for orders in user context
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => '/admin/orders/' . $record->id),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No orders found')
            ->emptyStateDescription('This user has not placed any orders yet.');
    }
}