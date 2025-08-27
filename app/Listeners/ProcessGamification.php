<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Services\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessGamification implements ShouldQueue
{
    use InteractsWithQueue;

    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Handle the event when order is completed
     */
    public function handle(OrderCompleted $event): void
    {
        try {
            $this->gamificationService->processOrderCompletion($event->order);
            
            Log::info("Gamification processed successfully for order: {$event->order->invoice_no}");
        } catch (\Exception $e) {
            Log::error("Failed to process gamification for order {$event->order->invoice_no}: {$e->getMessage()}");
            
            // Don't fail the job, just log the error
            // Gamification is non-critical to order completion
        }
    }

    /**
     * Handle a job failure
     */
    public function failed(OrderCompleted $event, $exception): void
    {
        Log::error("Gamification job failed for order {$event->order->invoice_no}: {$exception->getMessage()}");
    }
}