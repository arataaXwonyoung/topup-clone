<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Carbon\Carbon;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        // Today's stats
        $todayRevenue = Order::whereDate('created_at', today())
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total');
            
        $todayOrders = Order::whereDate('created_at', today())->count();
        
        // This month stats
        $monthRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total');
            
        // Pending orders
        $pendingOrders = Order::whereIn('status', ['PENDING', 'UNPAID'])->count();
        
        // Success rate
        $totalOrders = Order::count();
        $successOrders = Order::whereIn('status', ['PAID', 'DELIVERED'])->count();
        $successRate = $totalOrders > 0 ? round(($successOrders / $totalOrders) * 100, 1) : 0;
        
        // New users today
        $newUsersToday = User::whereDate('created_at', today())->count();
        
        return [
            Stat::make('Revenue Hari Ini', 'Rp ' . Number::format($todayRevenue, locale: 'id'))
                ->description($todayOrders . ' transaksi')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($this->getRevenueChart(7))
                ->color('success'),
                
            Stat::make('Revenue Bulan Ini', 'Rp ' . Number::format($monthRevenue, locale: 'id'))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->chart($this->getRevenueChart(30))
                ->color('primary'),
                
            Stat::make('Order Pending', Number::format($pendingOrders))
                ->description('Menunggu pembayaran')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Success Rate', $successRate . '%')
                ->description($successOrders . ' dari ' . $totalOrders . ' order')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate > 80 ? 'success' : 'danger'),
                
            Stat::make('User Baru Hari Ini', Number::format($newUsersToday))
                ->description('Total: ' . User::count())
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
                
            Stat::make('Total Pendapatan', 'Rp ' . Number::format(Order::whereIn('status', ['PAID', 'DELIVERED'])->sum('total'), locale: 'id'))
                ->description('Semua waktu')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
    
    protected function getRevenueChart(int $days): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->sum('total');
            $data[] = $revenue / 1000; // Convert to thousands for better chart display
        }
        return $data;
    }
}