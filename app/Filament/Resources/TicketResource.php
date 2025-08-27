<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    
    protected static ?string $navigationGroup = 'Support System';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Information')
                    ->schema([
                        Forms\Components\TextInput::make('ticket_number')
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
                            ]),
                        
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned To')
                            ->relationship(
                                'assignedTo', 
                                'name',
                                fn (Builder $query) => $query->where('is_admin', true)
                            )
                            ->searchable()
                            ->placeholder('Not assigned')
                            ->helperText('Assign to admin user'),
                        
                        Forms\Components\Select::make('category')
                            ->options([
                                'general' => 'General Inquiry',
                                'account' => 'Account Issue',
                                'payment' => 'Payment Problem',
                                'order' => 'Order Issue',
                                'technical' => 'Technical Support',
                                'refund' => 'Refund Request',
                                'complaint' => 'Complaint',
                                'suggestion' => 'Suggestion',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('general'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Priority & Status')
                    ->schema([
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required()
                            ->default('medium')
                            ->reactive(),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'waiting_customer' => 'Waiting for Customer',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->required()
                            ->default('open')
                            ->reactive(),
                        
                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->label('Resolved At')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['resolved', 'closed'])),
                        
                        Forms\Components\DateTimePicker::make('closed_at')
                            ->label('Closed At')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'closed'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Ticket Details')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Brief description of the issue')
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->placeholder('Detailed description of the issue...')
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('resolution')
                            ->label('Resolution/Solution')
                            ->placeholder('How was this issue resolved?...')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['resolved', 'closed']))
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->helperText('Additional ticket data (JSON format)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->placeholder('Not assigned')
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'gray',
                        'account' => 'info',
                        'payment' => 'warning',
                        'order' => 'primary',
                        'technical' => 'danger',
                        'refund' => 'success',
                        'complaint' => 'danger',
                        'suggestion' => 'info',
                        'other' => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'urgent' => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'in_progress' => 'warning',
                        'waiting_customer' => 'info',
                        'resolved' => 'success',
                        'closed' => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('replies_count')
                    ->label('Replies')
                    ->counts('replies')
                    ->alignCenter()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, H:i')
                    ->sortable()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('resolved_at')
                    ->label('Resolved')
                    ->dateTime('M d, H:i')
                    ->placeholder('Not resolved')
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'waiting_customer' => 'Waiting for Customer',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'general' => 'General Inquiry',
                        'account' => 'Account Issue',
                        'payment' => 'Payment Problem',
                        'order' => 'Order Issue',
                        'technical' => 'Technical Support',
                        'refund' => 'Refund Request',
                        'complaint' => 'Complaint',
                        'suggestion' => 'Suggestion',
                        'other' => 'Other',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Assigned To')
                    ->relationship(
                        'assignedTo', 
                        'name',
                        fn (Builder $query) => $query->where('is_admin', true)
                    )
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('unassigned')
                    ->query(fn (Builder $query): Builder => $query->whereNull('assigned_to'))
                    ->label('Unassigned'),
                
                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('created_at', '<', now()->subHours(24))
                            ->whereIn('status', ['open', 'in_progress'])
                    )
                    ->label('Overdue (>24h)'),
                
                Tables\Filters\Filter::make('high_priority')
                    ->query(fn (Builder $query): Builder => $query->whereIn('priority', ['high', 'urgent']))
                    ->label('High Priority'),
            ])
            ->actions([
                Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn ($record) => !$record->assigned_to)
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assign to Admin')
                            ->options(User::where('is_admin', true)->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'status' => 'in_progress',
                        ]);
                        
                        Notification::make()
                            ->title('Ticket assigned successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('change_status')
                    ->label('Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'waiting_customer' => 'Waiting for Customer',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('resolution')
                            ->label('Resolution (if resolving)')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['resolved', 'closed']))
                            ->required(fn (Forms\Get $get) => in_array($get('status'), ['resolved', 'closed'])),
                    ])
                    ->action(function ($record, array $data) {
                        $updateData = ['status' => $data['status']];
                        
                        if (in_array($data['status'], ['resolved', 'closed'])) {
                            $updateData['resolution'] = $data['resolution'];
                            $updateData['resolved_at'] = now();
                            
                            if ($data['status'] === 'closed') {
                                $updateData['closed_at'] = now();
                            }
                        }
                        
                        $record->update($updateData);
                        
                        Notification::make()
                            ->title('Ticket status updated')
                            ->success()
                            ->send();
                    }),
                
                Action::make('change_priority')
                    ->label('Priority')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['priority' => $data['priority']]);
                        
                        Notification::make()
                            ->title('Ticket priority updated')
                            ->success()
                            ->send();
                    }),
                
                Action::make('add_reply')
                    ->label('Reply')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('primary')
                    ->form([
                        Forms\Components\RichEditor::make('message')
                            ->required()
                            ->placeholder('Type your reply...'),
                        Forms\Components\Toggle::make('is_internal')
                            ->label('Internal Note')
                            ->helperText('Only visible to staff'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->replies()->create([
                            'user_id' => auth()->id(),
                            'message' => $data['message'],
                            'is_internal' => $data['is_internal'] ?? false,
                        ]);
                        
                        Notification::make()
                            ->title('Reply added successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'closed'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('assign_bulk')
                        ->label('Assign to Admin')
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('assigned_to')
                                ->label('Assign to Admin')
                                ->options(User::where('is_admin', true)->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update([
                                    'assigned_to' => $data['assigned_to'],
                                    'status' => 'in_progress',
                                ]);
                            }
                            
                            Notification::make()
                                ->title(count($records) . ' tickets assigned')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('change_status_bulk')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'open' => 'Open',
                                    'in_progress' => 'In Progress',
                                    'waiting_customer' => 'Waiting for Customer',
                                    'resolved' => 'Resolved',
                                    'closed' => 'Closed',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $updateData = ['status' => $data['status']];
                            
                            if (in_array($data['status'], ['resolved', 'closed'])) {
                                $updateData['resolved_at'] = now();
                                if ($data['status'] === 'closed') {
                                    $updateData['closed_at'] = now();
                                }
                            }
                            
                            foreach ($records as $record) {
                                $record->update($updateData);
                            }
                            
                            Notification::make()
                                ->title(count($records) . ' tickets updated')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No tickets found')
            ->emptyStateDescription('Support tickets will appear here as customers submit them.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RepliesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['open', 'in_progress'])->count();
    }
}