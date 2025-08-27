<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Game;
use App\Models\Denomination;
use App\Services\Payment\PaymentManager;
use App\Services\GameProviderService;
use App\Events\OrderCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected PaymentManager $paymentManager;
    protected PromoService $promoService;
    protected GameProviderService $gameProviderService;

    public function __construct(
        PaymentManager $paymentManager, 
        PromoService $promoService,
        GameProviderService $gameProviderService
    ) {
        $this->paymentManager = $paymentManager;
        $this->promoService = $promoService;
        $this->gameProviderService = $gameProviderService;
    }

    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $denomination = Denomination::findOrFail($data['denomination_id']);
            $game = Game::findOrFail($data['game_id']);

            $subtotal = $denomination->price * ($data['quantity'] ?? 1);
            $fee = $this->calculateFee($data['payment_method'] ?? 'QRIS');
            $discount = 0;

            // Apply promo if provided
            if (!empty($data['promo_code'])) {
                $promoValidation = $this->promoService->validatePromo(
                    $data['promo_code'],
                    $subtotal,
                    $game->id,
                    auth()->user()
                );

                if ($promoValidation['valid']) {
                    $discount = $promoValidation['discount'];
                }
            }

            $total = $subtotal - $discount + $fee;

            $order = Order::create([
                'user_id' => auth()->id(),
                'game_id' => $game->id,
                'denomination_id' => $denomination->id,
                'account_id' => $data['account_id'],
                'server_id' => $data['server_id'] ?? null,
                'username' => $data['username'] ?? null,
                'email' => $data['email'],
                'whatsapp' => $data['whatsapp'],
                'quantity' => $data['quantity'] ?? 1,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'promo_code' => $data['promo_code'] ?? null,
                'fee' => $fee,
                'total' => $total,
                'status' => 'UNPAID',
                'expires_at' => now()->addHours(3),
            ]);

            // Create payment
            $this->paymentManager->createPayment(
                $order,
                $data['payment_method'] ?? 'QRIS',
                ['channel' => $data['payment_channel'] ?? null]
            );

            // Increment promo usage
            if (!empty($data['promo_code']) && $discount > 0) {
                $promoValidation['promo']->incrementUsage();
            }

            return $order;
        });
    }

    protected function calculateFee(string $method): float
    {
        return match ($method) {
            'QRIS' => 1000,
            'VA' => 2500,
            'EWALLET' => 1500,
            'CC' => 3000,
            default => 0,
        };
    }

    public function checkExpiredOrders(): int
    {
        $expiredOrders = Order::where('status', 'UNPAID')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiredOrders as $order) {
            $order->markAsExpired();
            
            if ($order->payment) {
                $order->payment->markAsExpired();
            }
        }

        return $expiredOrders->count();
    }

    public function fulfillOrder(Order $order): bool
    {
        try {
            Log::info("Starting fulfillment for order {$order->invoice_no}");

            // Call real game provider API
            $topUpResult = $this->gameProviderService->topUpAccount($order);

            if ($topUpResult['success']) {
                // Update order with delivery data
                $order->update([
                    'status' => 'DELIVERED',
                    'delivered_at' => now(),
                    'delivery_data' => json_encode($topUpResult['delivery_data']),
                    'provider_transaction_id' => $topUpResult['transaction_id']
                ]);

                Log::info("Order {$order->invoice_no} fulfilled successfully", [
                    'provider_trx_id' => $topUpResult['transaction_id'],
                    'game' => $order->game->name
                ]);

                // Trigger gamification after successful fulfillment
                $this->completeOrder($order);

                // Send success notification to user
                $this->sendDeliveryNotification($order, $topUpResult);

                return true;
            } else {
                // Mark as failed if top-up failed
                $order->update([
                    'status' => 'FAILED',
                    'failure_reason' => $topUpResult['message']
                ]);

                Log::error("Order {$order->invoice_no} fulfillment failed", [
                    'error' => $topUpResult['message'],
                    'error_code' => $topUpResult['error_code'] ?? null
                ]);

                return false;
            }

        } catch (\Exception $e) {
            Log::error("Fulfillment exception for order {$order->invoice_no}: {$e->getMessage()}");
            
            $order->update([
                'status' => 'FAILED', 
                'failure_reason' => 'System error: ' . $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Send delivery notification to user
     */
    protected function sendDeliveryNotification(Order $order, array $topUpResult): void
    {
        try {
            // Send WhatsApp notification
            $message = "✅ *TOP UP BERHASIL!*\n\n";
            $message .= "🎮 Game: {$order->game->name}\n";
            $message .= "💎 Item: {$order->denomination->name}\n";
            $message .= "🎯 Target: {$order->account_id}\n";
            $message .= "📧 Order: #{$order->invoice_no}\n";
            $message .= "⏰ Estimasi: {$topUpResult['delivery_time']}\n\n";
            $message .= "Terima kasih telah menggunakan layanan kami! 🙏";

            // TODO: Integrate with WhatsApp API
            Log::info("WhatsApp notification sent to {$order->whatsapp}", [
                'order_id' => $order->id,
                'message' => $message
            ]);

            // Send email notification
            // TODO: Queue email job

        } catch (\Exception $e) {
            Log::error("Failed to send delivery notification: {$e->getMessage()}");
        }
    }

    /**
     * Mark order as completed and trigger gamification
     */
    public function completeOrder(Order $order): bool
    {
        try {
            DB::transaction(function () use ($order) {
                // Update order status if not already delivered
                if ($order->status !== 'DELIVERED') {
                    $order->update([
                        'status' => 'DELIVERED',
                        'delivered_at' => now()
                    ]);
                }

                Log::info("Order {$order->invoice_no} marked as delivered");

                // Trigger gamification event
                event(new OrderCompleted($order));

                Log::info("OrderCompleted event dispatched for {$order->invoice_no}");
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to complete order {$order->invoice_no}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Process webhook from payment gateway
     */
    public function handlePaymentWebhook(array $webhookData): bool
    {
        try {
            // Find order by invoice number or external ID
            $order = Order::where('invoice_no', $webhookData['invoice_no'] ?? null)
                ->orWhere('external_id', $webhookData['external_id'] ?? null)
                ->first();

            if (!$order) {
                Log::warning("Order not found for webhook data", $webhookData);
                return false;
            }

            // Update order status based on webhook
            switch ($webhookData['status']) {
                case 'PAID':
                case 'SUCCESS':
                    $order->update([
                        'status' => 'PAID',
                        'paid_at' => now()
                    ]);
                    
                    // Auto-fulfill digital products immediately
                    if ($order->game->is_digital ?? true) {
                        $this->fulfillOrder($order);
                    }
                    break;

                case 'FAILED':
                case 'EXPIRED':
                    $order->update(['status' => 'FAILED']);
                    break;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Webhook processing failed: {$e->getMessage()}", $webhookData);
            return false;
        }
    }
}