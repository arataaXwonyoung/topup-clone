<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_analytics')
                ->label('View Analytics')
                ->icon('heroicon-o-chart-pie')
                ->color('primary')
                ->url(fn () => static::getResource()::getUrl('analytics')),
            
            Actions\Action::make('revenue_reports')
                ->label('Revenue Reports')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->url(fn () => static::getResource()::getUrl('revenue')),
            
            Actions\Action::make('customer_reports')
                ->label('Customer Reports')
                ->icon('heroicon-o-users')
                ->color('info')
                ->url(fn () => static::getResource()::getUrl('customers')),
        ];
    }
}