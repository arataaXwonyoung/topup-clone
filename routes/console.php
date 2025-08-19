<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Console commands

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled tasks
Schedule::command('orders:expire')->everyMinute();
Schedule::command('queue:work --stop-when-empty')->everyMinute();
Schedule::command('telescope:prune')->daily();

// Clean old webhook logs
Schedule::call(function () {
    \App\Models\WebhookLog::where('created_at', '<', now()->subDays(30))->delete();
})->daily();

// Generate daily report (optional)
Schedule::call(function () {
    $yesterday = now()->subDay();
    $orders = \App\Models\Order::whereDate('created_at', $yesterday)
        ->whereIn('status', ['PAID', 'DELIVERED'])
        ->get();
    
    $totalRevenue = $orders->sum('total');
    $totalOrders = $orders->count();
    
    \Log::info('Daily Report', [
        'date' => $yesterday->toDateString(),
        'total_orders' => $totalOrders,
        'total_revenue' => $totalRevenue,
    ]);
})->dailyAt('00:01');