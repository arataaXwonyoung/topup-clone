<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto expire orders every minute
        $schedule->command('orders:expire')->everyMinute();
        
        // Clean old webhook logs weekly
        $schedule->command('webhook:clean')->weekly();
        
        // Generate daily reports
        $schedule->command('reports:daily')->dailyAt('00:00');
        
        // Backup database daily
        $schedule->command('backup:run')->dailyAt('01:00');
        
        // Clear expired sessions
        $schedule->command('session:gc')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}