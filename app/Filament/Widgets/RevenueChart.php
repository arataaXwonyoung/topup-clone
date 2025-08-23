<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue Last 30 Days';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';
    
    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->sum('total');
                
            $data[] = $revenue;
            $labels[] = $date->format('d M');
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(250, 204, 21, 0.1)',
                    'borderColor' => 'rgb(250, 204, 21)',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
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
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }",
                    ],
                ],
            ],
        ];
    }
}