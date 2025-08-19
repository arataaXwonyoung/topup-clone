<?php

namespace App\Listeners;

use App\Events\PaymentStatusUpdated;
use App\Jobs\AutoFulfillmentJob;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessPaymentUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PaymentStatusUpdated $event): void
    {
        $payment = $event->payment;
        $order = $payment->order;
        
        Log::info('Processing payment update', [
            'payment_id' => $payment->id,
            'order_id' => $order->id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
        ]);

        // Update order status based on payment status
        if ($event->newStatus === 'PAID' && $order->status !== 'PAID') {
            $order->markAsPaid();
            
            // Dispatch fulfillment job
            dispatch(new AutoFulfillmentJob($order))
                ->delay(now()->addSeconds(10));
            
            // Send notification to customer
            $this->sendPaymentSuccessNotification($order);
            
        } elseif ($event->newStatus === 'EXPIRED' && $order->status !== 'EXPIRED') {
            $order->markAsExpired();
            
        } elseif ($event->newStatus === 'FAILED' && $order->status !== 'FAILED') {
            $order->update(['status' => 'FAILED']);
        }
    }

    protected function sendPaymentSuccessNotification(Order $order): void
    {
        // Here you would send email/WhatsApp notification
        // For now, just log it
        Log::info('Payment success notification queued', [
            'order_id' => $order->id,
            'email' => $order->email,
            'whatsapp' => $order->whatsapp,
        ]);
    }

    public function failed(PaymentStatusUpdated $event, \Throwable $exception): void
    {
        Log::error('Failed to process payment update', [
            'payment_id' => $event->payment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}