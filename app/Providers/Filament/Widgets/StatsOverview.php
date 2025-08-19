<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\Game;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $todayRevenue = Order::whereDate('created_at', today())
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total') ?? 0;
            
        $totalOrders = Order::count();
        $totalUsers = User::where('is_admin', false)->count();
        $totalGames = Game::where('is_active', true)->count();
        
        return [
            Stat::make('Today Revenue', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description('Revenue hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Total Orders', number_format($totalOrders))
                ->description('Total pesanan')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
                
            Stat::make('Total Users', number_format($totalUsers))
                ->description('Total pengguna')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
                
            Stat::make('Active Games', number_format($totalGames))
                ->description('Game aktif')
                ->descriptionIcon('heroicon-m-puzzle-piece')
                ->color('warning'),
        ];
    }
}