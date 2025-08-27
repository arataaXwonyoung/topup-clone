<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'User Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(191)
                            ->placeholder('Full Name'),
                        
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191)
                            ->placeholder('user@example.com'),
                        
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+62812345678')
                            ->helperText('Include country code'),
                        
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->helperText('Leave empty if email is not verified'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Account Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_admin')
                            ->label('Admin Access')
                            ->helperText('Grant admin panel access'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Account Active')
                            ->helperText('Enable/disable user account')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_suspended')
                            ->label('Suspended')
                            ->helperText('Temporarily suspend user account'),
                        
                        Forms\Components\DateTimePicker::make('suspended_until')
                            ->label('Suspended Until')
                            ->helperText('Automatic unsuspend date (optional)')
                            ->visible(fn (Forms\Get $get) => $get('is_suspended')),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Account Limits')
                    ->schema([
                        Forms\Components\TextInput::make('daily_limit')
                            ->label('Daily Transaction Limit')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('1000000')
                            ->helperText('Maximum daily transaction amount'),
                        
                        Forms\Components\TextInput::make('monthly_limit')
                            ->label('Monthly Transaction Limit')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('10000000')
                            ->helperText('Maximum monthly transaction amount'),
                        
                        Forms\Components\TextInput::make('max_orders_per_day')
                            ->label('Max Orders Per Day')
                            ->numeric()
                            ->default(10)
                            ->helperText('Maximum number of orders per day'),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Security')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->placeholder('Enter new password')
                            ->helperText('Leave empty to keep current password'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Admin Notes')
                            ->placeholder('Internal notes about this user...')
                            ->rows(3)
                            ->helperText('Only visible to admins'),
                    ])
                    ->columns(1),
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
                
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->placeholder('No phone')
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Total Orders')
                    ->counts('orders')
                    ->sortable()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Spent')
                    ->getStateUsing(function ($record) {
                        return $record->orders()
                            ->whereIn('status', ['PAID', 'DELIVERED'])
                            ->sum('total');
                    })
                    ->money('IDR')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('last_order_date')
                    ->label('Last Order')
                    ->getStateUsing(function ($record) {
                        return $record->orders()
                            ->latest()
                            ->first()?->created_at;
                    })
                    ->dateTime('M d, H:i')
                    ->placeholder('No orders')
                    ->color('gray'),
                
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->email_verified_at !== null)
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning'),
                
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('info')
                    ->falseColor('gray'),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->is_suspended) {
                            return $record->suspended_until && $record->suspended_until < now() 
                                ? 'Auto-Unsuspend' 
                                : 'Suspended';
                        }
                        if (!$record->is_active) {
                            return 'Inactive';
                        }
                        return 'Active';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'gray',
                        'Suspended' => 'danger',
                        'Auto-Unsuspend' => 'warning',
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Account Active'),
                
                Tables\Filters\TernaryFilter::make('is_suspended')
                    ->label('Suspended'),
                
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('Admin Users'),
                
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->nullable(),
                
                Tables\Filters\Filter::make('has_orders')
                    ->query(fn (Builder $query): Builder => $query->has('orders'))
                    ->label('Has Orders'),
                
                Tables\Filters\Filter::make('big_spenders')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('orders', function ($q) {
                            $q->whereIn('status', ['PAID', 'DELIVERED'])
                                ->havingRaw('SUM(total) > 1000000');
                        })
                    )
                    ->label('Big Spenders (>1M)'),
                
                Tables\Filters\Filter::make('recent_customers')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('orders', function ($q) {
                            $q->where('created_at', '>=', now()->subDays(30));
                        })
                    )
                    ->label('Active Last 30 Days'),
            ])
            ->actions([
                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn ($record) => !$record->is_suspended)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Suspension Reason')
                            ->required()
                            ->placeholder('Reason for suspension...'),
                        Forms\Components\DateTimePicker::make('suspended_until')
                            ->label('Suspend Until (Optional)')
                            ->helperText('Leave empty for permanent suspension'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'is_suspended' => true,
                            'suspended_until' => $data['suspended_until'] ?? null,
                            'suspension_reason' => $data['reason'],
                        ]);
                        
                        Notification::make()
                            ->title('User suspended successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('unsuspend')
                    ->label('Unsuspend')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->is_suspended)
                    ->action(function ($record) {
                        $record->update([
                            'is_suspended' => false,
                            'suspended_until' => null,
                            'suspension_reason' => null,
                        ]);
                        
                        Notification::make()
                            ->title('User unsuspended successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reset_password')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('new_password')
                            ->label('New Password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->placeholder('Enter new password'),
                        Forms\Components\Toggle::make('notify_user')
                            ->label('Notify User')
                            ->helperText('Send email notification about password reset')
                            ->default(true),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'password' => Hash::make($data['new_password']),
                        ]);
                        
                        if ($data['notify_user']) {
                            // Here you would send notification to user
                            // Mail::to($record->email)->send(new PasswordResetNotification());
                        }
                        
                        Notification::make()
                            ->title('Password reset successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('set_limits')
                    ->label('Set Limits')
                    ->icon('heroicon-o-scale')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('daily_limit')
                            ->label('Daily Limit')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('1000000'),
                        Forms\Components\TextInput::make('monthly_limit')
                            ->label('Monthly Limit')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('10000000'),
                        Forms\Components\TextInput::make('max_orders_per_day')
                            ->label('Max Orders Per Day')
                            ->numeric()
                            ->placeholder('10'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'daily_limit' => $data['daily_limit'],
                            'monthly_limit' => $data['monthly_limit'],
                            'max_orders_per_day' => $data['max_orders_per_day'],
                        ]);
                        
                        Notification::make()
                            ->title('User limits updated successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('view_orders')
                    ->label('View Orders')
                    ->icon('heroicon-o-shopping-bag')
                    ->url(fn ($record) => '/admin/orders?tableFilters[user_id][value]=' . $record->id)
                    ->openUrlInNewTab(),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete User')
                    ->modalDescription('Are you sure you want to delete this user? This will also delete all related orders and data.')
                    ->modalSubmitActionLabel('Yes, delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('suspend_bulk')
                        ->label('Suspend Selected')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Suspension Reason')
                                ->required()
                                ->placeholder('Reason for bulk suspension...'),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update([
                                    'is_suspended' => true,
                                    'suspension_reason' => $data['reason'],
                                ]);
                            }
                            
                            Notification::make()
                                ->title(count($records) . ' users suspended successfully')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('export_users')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($records) {
                            // Export functionality would go here
                            Notification::make()
                                ->title('Export started')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No users found')
            ->emptyStateDescription('Users will appear here as they register on your platform.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('created_at', '>=', now()->subDays(7))->count();
    }
}