<?php

namespace App\Filament\Widgets;

use App\Models\Game;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PopularGamesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Popular Games';
    
    protected static ?string $description = 'Top 5 games by transaction count (last 30 days)';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $popularGames = Order::query()
            ->select('game_id', DB::raw('COUNT(*) as transaction_count'), DB::raw('SUM(total) as total_revenue'))
            ->with('game:id,name')
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('game_id')
            ->orderByDesc('transaction_count')
            ->limit(5)
            ->get();

        $labels = $popularGames->map(fn ($item) => $item->game->name ?? 'Unknown')->toArray();
        $transactionData = $popularGames->pluck('transaction_count')->toArray();
        $revenueData = $popularGames->map(fn ($item) => round($item->total_revenue / 1000000, 2))->toArray(); // Convert to millions

        return [
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data' => $transactionData,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
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
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}