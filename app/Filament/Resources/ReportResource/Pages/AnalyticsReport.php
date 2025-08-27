<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\Order;
use App\Models\User;
use App\Models\Game;
use Filament\Resources\Pages\Page;
use Filament\Actions;
use Filament\Forms;
use Carbon\Carbon;

class AnalyticsReport extends Page
{
    protected static string $resource = ReportResource::class;

    protected static string $view = 'filament.resources.report-resource.pages.analytics-report';
    
    protected static ?string $title = 'Analytics Dashboard';

    public $dateFrom;
    public $dateTo;

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_analytics')
                ->label('Export Analytics')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Forms\Components\DatePicker::make('date_from')
                        ->label('From Date')
                        ->default($this->dateFrom)
                        ->required(),
                    Forms\Components\DatePicker::make('date_to')
                        ->label('To Date')
                        ->default($this->dateTo)
                        ->required(),
                    Forms\Components\Select::make('format')
                        ->options([
                            'pdf' => 'PDF Report',
                            'excel' => 'Excel Spreadsheet',
                        ])
                        ->default('pdf')
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Export analytics functionality
                    $this->notify('success', 'Analytics report export started');
                }),
        ];
    }

    public function getAnalyticsData(): array
    {
        $dateFrom = Carbon::parse($this->dateFrom);
        $dateTo = Carbon::parse($this->dateTo);

        return [
            'overview' => [
                'total_revenue' => Order::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->whereIn('status', ['PAID', 'DELIVERED'])
                    ->sum('total'),
                'total_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'successful_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->whereIn('status', ['PAID', 'DELIVERED'])
                    ->count(),
                'new_customers' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'conversion_rate' => $this->calculateConversionRate($dateFrom, $dateTo),
                'average_order_value' => $this->calculateAverageOrderValue($dateFrom, $dateTo),
            ],
            'top_games' => Game::withCount([
                'orders' => function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->whereIn('status', ['PAID', 'DELIVERED']);
                }
            ])
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get(),
            'revenue_by_game' => Order::selectRaw('games.name, SUM(orders.total) as revenue')
                ->join('games', 'orders.game_id', '=', 'games.id')
                ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                ->whereIn('orders.status', ['PAID', 'DELIVERED'])
                ->groupBy('games.id', 'games.name')
                ->orderBy('revenue', 'desc')
                ->limit(10)
                ->get(),
            'daily_revenue' => $this->getDailyRevenue($dateFrom, $dateTo),
            'payment_methods' => Order::selectRaw('payment_method, COUNT(*) as count, SUM(total) as revenue')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->groupBy('payment_method')
                ->orderBy('revenue', 'desc')
                ->get(),
        ];
    }

    private function calculateConversionRate($dateFrom, $dateTo): float
    {
        $totalOrders = Order::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $successfulOrders = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->count();
            
        return $totalOrders > 0 ? round(($successfulOrders / $totalOrders) * 100, 2) : 0;
    }

    private function calculateAverageOrderValue($dateFrom, $dateTo): float
    {
        return Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->avg('total') ?: 0;
    }

    private function getDailyRevenue($dateFrom, $dateTo): array
    {
        $data = Order::selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $data->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d'),
                'revenue' => $item->revenue,
                'orders' => $item->orders,
            ];
        })->toArray();
    }
}