<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class SystemLogs extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'System';
    protected static string $view = 'filament.pages.system-logs';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(WebhookLog::query())
            ->columns([
                Tables\Columns\TextColumn::make('provider')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'midtrans' => 'warning',
                        'xendit' => 'info',
                        'tripay' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('event_type')
                    ->label('Event'),
                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'PROCESSED',
                        'warning' => 'PENDING',
                        'danger' => 'FAILED',
                        'gray' => 'IGNORED',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        'midtrans' => 'Midtrans',
                        'xendit' => 'Xendit',
                        'tripay' => 'Tripay',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'Pending',
                        'PROCESSED' => 'Processed',
                        'FAILED' => 'Failed',
                        'IGNORED' => 'Ignored',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn ($record) => view('filament.modals.webhook-log-details', ['log' => $record])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}