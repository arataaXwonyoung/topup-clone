<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Order Status Distribution';
    protected static ?int $sort = 3;
    
    protected function getData(): array
    {
        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        return [
            'datasets' => [
                [
                    'data' => array_values($statusCounts),
                    'backgroundColor' => [
                        '#10b981', // DELIVERED - green
                        '#3b82f6', // PAID - blue
                        '#f59e0b', // PENDING - yellow
                        '#ef4444', // FAILED - red
                        '#6b7280', // EXPIRED - gray
                    ],
                ],
            ],
            'labels' => array_keys($statusCounts),
        ];
    }
    
    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}