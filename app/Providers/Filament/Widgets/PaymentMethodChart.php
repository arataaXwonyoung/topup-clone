<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class PaymentMethodChart extends ChartWidget
{
    protected static ?string $heading = 'Metode Pembayaran';
    protected static ?int $sort = 3;
    
    protected function getData(): array
    {
        $methods = Payment::selectRaw('method, COUNT(*) as count')
            ->whereIn('status', ['PAID'])
            ->groupBy('method')
            ->get();
        
        return [
            'datasets' => [
                [
                    'label' => 'Transaksi',
                    'data' => $methods->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#fbbf24', // QRIS
                        '#60a5fa', // VA
                        '#34d399', // E-Wallet
                        '#f87171', // CC
                    ],
                ],
            ],
            'labels' => $methods->pluck('method')->map(fn($m) => strtoupper($m))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}