<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Game;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Reports & Analytics';
    protected static string $view = 'filament.pages.analytics';
    
    public $dateFrom;
    public $dateTo;
    public $selectedGame = null;
    
    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }
    
    public function getAnalyticsData(): array
    {
        $dateFrom = Carbon::parse($this->dateFrom);
        $dateTo = Carbon::parse($this->dateTo);
        
        // Revenue Analytics
        $totalRevenue = Order::whereIn('status', ['PAID', 'DELIVERED'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('total');
            
        $averageOrderValue = Order::whereIn('status', ['PAID', 'DELIVERED'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->avg('total');
            
        // Top Games by Revenue
        $topGames = Game::withSum(['orders' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereIn('status', ['PAID', 'DELIVERED'])
                    ->whereBetween('created_at', [$dateFrom, $dateTo]);
            }], 'total')
            ->orderByDesc('orders_sum_total')
            ->limit(10)
            ->get();
            
        // Payment Method Distribution
        $paymentMethods = Payment::select('method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->whereIn('status', ['PAID'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('method')
            ->get();
            
        // Customer Analytics
        $newCustomers = User::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('is_admin', false)
            ->count();
            
        $returningCustomers = Order::whereIn('status', ['PAID', 'DELIVERED'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->distinct('user_id')
            ->whereNotNull('user_id')
            ->count('user_id');
            
        // Hourly Distribution
        $hourlyOrders = Order::whereIn('status', ['PAID', 'DELIVERED'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
            
        // Success Rate
        $totalOrders = Order::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $successfulOrders = Order::whereIn('status', ['PAID', 'DELIVERED'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();
        $successRate = $totalOrders > 0 ? ($successfulOrders / $totalOrders) * 100 : 0;
        
        return [
            'totalRevenue' => $totalRevenue,
            'averageOrderValue' => $averageOrderValue,
            'topGames' => $topGames,
            'paymentMethods' => $paymentMethods,
            'newCustomers' => $newCustomers,
            'returningCustomers' => $returningCustomers,
            'hourlyOrders' => $hourlyOrders,
            'successRate' => $successRate,
            'totalOrders' => $totalOrders,
            'successfulOrders' => $successfulOrders,
        ];
    }
    
    public function exportReport()
    {
        // Implementation for exporting report to Excel/PDF
        // You can use Laravel Excel package here
    }
}