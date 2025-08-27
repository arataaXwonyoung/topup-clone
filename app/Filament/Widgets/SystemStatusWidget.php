<?php

namespace App\Filament\Widgets;

use App\Models\ApiProvider;
use App\Models\Order;
use App\Models\Payment;
use Filament\Widgets\Widget;

class SystemStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.system-status-widget';
    
    protected static ?int $sort = 10;
    
    protected static ?string $heading = 'System Status';

    public function getSystemStatus(): array
    {
        return [
            'api_providers' => [
                'total' => ApiProvider::count(),
                'active' => ApiProvider::where('is_active', true)->count(),
                'payment_providers' => ApiProvider::where('type', 'payment')->where('is_active', true)->count(),
                'topup_providers' => ApiProvider::where('type', 'topup')->where('is_active', true)->count(),
            ],
            'recent_activity' => [
                'pending_orders' => Order::where('status', 'PENDING')->count(),
                'processing_orders' => Order::where('status', 'PROCESSING')->count(),
                'pending_payments' => Payment::where('status', 'pending')->count(),
                'failed_payments_today' => Payment::where('status', 'failed')
                    ->whereDate('created_at', today())
                    ->count(),
            ],
            'health_checks' => [
                'database' => $this->checkDatabaseConnection(),
                'storage' => $this->checkStorageAccess(),
                'queue' => $this->checkQueueStatus(),
            ],
        ];
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkStorageAccess(): bool
    {
        try {
            return \Storage::disk('public')->exists('');
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkQueueStatus(): bool
    {
        // Simple queue health check
        return true; // In production, implement proper queue monitoring
    }
}