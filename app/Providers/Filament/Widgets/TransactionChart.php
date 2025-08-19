<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TransactionChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Transaksi';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    
    public ?string $filter = '7';

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $data = [];
        $labels = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            
            // Get data by status
            $paid = Order::whereDate('created_at', $date)
                ->whereIn('status', ['PAID', 'DELIVERED'])
                ->count();
                
            $pending = Order::whereDate('created_at', $date)
                ->whereIn('status', ['PENDING', 'UNPAID'])
                ->count();
                
            $failed = Order::whereDate('created_at', $date)
                ->whereIn('status', ['FAILED', 'EXPIRED'])
                ->count();
            
            $data['paid'][] = $paid;
            $data['pending'][] = $pending;
            $data['failed'][] = $failed;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Success',
                    'data' => $data['paid'],
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
                [
                    'label' => 'Pending',
                    'data' => $data['pending'],
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#f59e0b',
                ],
                [
                    'label' => 'Failed',
                    'data' => $data['failed'],
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#ef4444',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Hari',
            '30' => '30 Hari',
            '90' => '3 Bulan',
        ];
    }
}