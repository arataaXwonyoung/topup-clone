<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Daily Revenue Trend';
    
    protected static ?string $description = 'Revenue over the last 14 days';
    
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $revenueData = Order::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as daily_revenue'),
                DB::raw('COUNT(*) as daily_transactions')
            )
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Create array of last 14 days
        $dates = collect();
        for ($i = 13; $i >= 0; $i--) {
            $dates->push(now()->subDays($i)->format('Y-m-d'));
        }

        // Map revenue data to dates
        $revenues = $dates->map(function ($date) use ($revenueData) {
            $dayData = $revenueData->firstWhere('date', $date);
            return $dayData ? round($dayData->daily_revenue / 1000000, 2) : 0; // Convert to millions
        });

        $transactions = $dates->map(function ($date) use ($revenueData) {
            $dayData = $revenueData->firstWhere('date', $date);
            return $dayData ? $dayData->daily_transactions : 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Million IDR)',
                    'data' => $revenues->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Transactions',
                    'data' => $transactions->toArray(),
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $dates->map(fn ($date) => \Carbon\Carbon::parse($date)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (Million IDR)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Transactions',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}