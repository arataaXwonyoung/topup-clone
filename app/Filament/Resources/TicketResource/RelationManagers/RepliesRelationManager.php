<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RepliesRelationManager extends RelationManager
{
    protected static string $relationship = 'replies';
    
    protected static ?string $title = 'Replies & Notes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\RichEditor::make('message')
                    ->required()
                    ->placeholder('Type your reply...')
                    ->columnSpanFull(),
                
                Forms\Components\Toggle::make('is_internal')
                    ->label('Internal Note')
                    ->helperText('Only visible to staff'),
                
                Forms\Components\FileUpload::make('attachments')
                    ->multiple()
                    ->directory('ticket-attachments')
                    ->maxFiles(5)
                    ->acceptedFileTypes(['image/*', 'application/pdf', '.doc', '.docx', '.txt'])
                    ->helperText('Max 5 files, images/PDFs/documents only'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('From')
                    ->weight('bold')
                    ->color(fn ($record) => $record->user->is_admin ? 'info' : 'primary'),
                
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->html()
                    ->limit(100)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = strip_tags($column->getState());
                        return strlen($state) > 100 ? $state : null;
                    }),
                
                Tables\Columns\IconColumn::make('is_internal')
                    ->label('Internal')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('warning')
                    ->falseColor('success'),
                
                Tables\Columns\TextColumn::make('attachments')
                    ->label('Files')
                    ->getStateUsing(fn ($record) => is_array($record->attachments) ? count($record->attachments) : 0)
                    ->badge()
                    ->color('info')
                    ->placeholder('No files'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Posted')
                    ->dateTime('M d, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_internal')
                    ->label('Internal Notes'),
                
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Posted By')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn ($record) => $record->user_id === auth()->id()),
                ]),
            ])
            ->defaultSort('created_at', 'asc')
            ->emptyStateHeading('No replies yet')
            ->emptyStateDescription('Replies and internal notes will appear here.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
            ]);
    }
}