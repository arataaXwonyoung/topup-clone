<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static string $view = 'filament.pages.reports';
    
    protected static ?string $navigationGroup = 'Reports';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filter')
                    ->schema([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From Date')
                            ->required(),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To Date')
                            ->required(),
                        Forms\Components\Select::make('game_id')
                            ->label('Game')
                            ->options(Game::pluck('name', 'id'))
                            ->placeholder('All Games'),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }
    
    public function generate(): void
    {
        $data = $this->form->getState();
        
        // Generate report logic here
        $this->dispatch('reportGenerated', $data);
    }
    
    public function getReportData(): array
    {
        $dateFrom = Carbon::parse($this->data['date_from'] ?? now()->startOfMonth());
        $dateTo = Carbon::parse($this->data['date_to'] ?? now()->endOfMonth());
        $gameId = $this->data['game_id'] ?? null;
        
        $query = Order::whereIn('status', ['PAID', 'DELIVERED'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);
            
        if ($gameId) {
            $query->where('game_id', $gameId);
        }
        
        return [
            'total_revenue' => $query->sum('total'),
            'total_orders' => $query->count(),
            'average_order' => $query->avg('total'),
            'top_games' => Game::withSum(['orders' => function ($query) use ($dateFrom, $dateTo) {
                    $query->whereIn('status', ['PAID', 'DELIVERED'])
                        ->whereBetween('created_at', [$dateFrom, $dateTo]);
                }], 'total')
                ->orderByDesc('orders_sum_total')
                ->limit(5)
                ->get(),
        ];
    }
}