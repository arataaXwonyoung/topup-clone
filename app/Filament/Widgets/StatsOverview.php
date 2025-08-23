<?php
// app/Filament/Widgets/StatsOverview.php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\Game;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $todayRevenue = Order::whereDate('created_at', Carbon::today())
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total');
            
        $yesterdayRevenue = Order::whereDate('created_at', Carbon::yesterday())
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total');
            
        $revenueChange = $yesterdayRevenue > 0 
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
            : 0;
            
        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total');
            
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', Carbon::today())->count();
        
        $activeGames = Game::where('is_active', true)->count();
        
        return [
            Stat::make('Today\'s Revenue', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description($revenueChange >= 0 ? "↑ {$revenueChange}%" : "↓ {$revenueChange}%")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChart(7)),
                
            Stat::make('Monthly Revenue', 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'))
                ->description('Current month total')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
                
            Stat::make('Total Users', number_format($totalUsers))
                ->description("+{$newUsersToday} today")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
                
            Stat::make('Active Games', $activeGames)
                ->description('Available for top-up')
                ->descriptionIcon('heroicon-m-puzzle-piece')
                ->color('primary'),
        ];
    }
    
    protected function getRevenueChart($days): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $revenue = Order::whereDate('created_at', now()->subDays($i))
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->sum('total');
            $data[] = $revenue / 1000; // Convert to thousands
        }
        return $data;
    }
}