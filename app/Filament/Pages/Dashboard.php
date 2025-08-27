<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleDarkMode')
                ->label('Toggle Dark Mode')
                ->icon('heroicon-o-moon')
                ->action(function () {
                    // Dark mode toggle logic (simpan preferensi di session)
                    session(['dark_mode' => !session('dark_mode', false)]);

                    // Kembali ke halaman sebelumnya
                    $this->redirect(request()->header('Referer') ?: url()->current());
                }),
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
    
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverviewWidget::class,
            \App\Filament\Widgets\SystemStatusWidget::class,
            \App\Filament\Widgets\RevenueChartWidget::class,
            \App\Filament\Widgets\PopularGamesChartWidget::class,
            \App\Filament\Widgets\LatestOrdersWidget::class,
        ];
    }
}
