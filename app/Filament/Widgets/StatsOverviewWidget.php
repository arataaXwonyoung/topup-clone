<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Calculate Total GMV (Gross Merchandise Value)
        $totalGMV = Order::whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total');
        
        // Calculate GMV growth (compared to previous period)
        $previousPeriodGMV = Order::whereIn('status', ['PAID', 'DELIVERED'])
            ->where('created_at', '<', now()->subDays(30))
            ->where('created_at', '>=', now()->subDays(60))
            ->sum('total');
        
        $gmvGrowth = $previousPeriodGMV > 0 
            ? (($totalGMV - $previousPeriodGMV) / $previousPeriodGMV) * 100 
            : 0;
        
        // Daily Transactions (today)
        $dailyTransactions = Order::whereDate('created_at', today())
            ->count();
        
        // Daily transactions yesterday for comparison
        $yesterdayTransactions = Order::whereDate('created_at', today()->subDay())
            ->count();
        
        $transactionGrowth = $yesterdayTransactions > 0 
            ? (($dailyTransactions - $yesterdayTransactions) / $yesterdayTransactions) * 100 
            : 0;
        
        // Active Users (users who made orders in last 30 days)
        $activeUsers = User::whereHas('orders', function($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        })->count();
        
        // Payment Success Rate
        $totalPayments = Payment::where('created_at', '>=', now()->subDays(7))->count();
        $successfulPayments = Payment::where('created_at', '>=', now()->subDays(7))
            ->where('status', 'PAID')
            ->count();
        
        $successRate = $totalPayments > 0 ? ($successfulPayments / $totalPayments) * 100 : 0;
        
        return [
            Stat::make('Total GMV', 'Rp ' . Number::format($totalGMV, locale: 'id'))
                ->description($gmvGrowth >= 0 ? "{$gmvGrowth}% increase" : "{$gmvGrowth}% decrease")
                ->descriptionIcon($gmvGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($gmvGrowth >= 0 ? 'success' : 'danger')
                ->chart([65, 61, 68, 63, 78, 65, 68]),
            
            Stat::make('Daily Transactions', $dailyTransactions)
                ->description($transactionGrowth >= 0 ? "{$transactionGrowth}% increase" : "{$transactionGrowth}% decrease")
                ->descriptionIcon($transactionGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($transactionGrowth >= 0 ? 'success' : 'danger')
                ->chart([40, 45, 52, 48, 55, 50, 58]),
            
            Stat::make('Active Users', $activeUsers)
                ->description('Last 30 days')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart([30, 35, 40, 38, 42, 39, 45]),
            
            Stat::make('Payment Success Rate', round($successRate, 1) . '%')
                ->description('Last 7 days')
                ->descriptionIcon($successRate >= 95 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($successRate >= 95 ? 'success' : ($successRate >= 90 ? 'warning' : 'danger'))
                ->chart([95, 94, 96, 95, 97, 94, 96]),
        ];
    }
    
    protected function getColumns(): int
    {
        return 2;
    }
}