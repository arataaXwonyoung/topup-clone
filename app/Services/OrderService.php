<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Game;
use App\Models\Denomination;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected PaymentManager $paymentManager;
    protected PromoService $promoService;

    public function __construct(PaymentManager $paymentManager, PromoService $promoService)
    {
        $this->paymentManager = $paymentManager;
        $this->promoService = $promoService;
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
        // Here you would integrate with actual game provider API
        // For now, we'll simulate successful fulfillment
        
        try {
            // Simulate API call to game provider
            sleep(1);
            
            $deliveryData = [
                'transaction_id' => 'TRX' . time(),
                'delivered_at' => now()->toDateTimeString(),
                'status' => 'SUCCESS',
            ];

            $order->markAsDelivered(json_encode($deliveryData));
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Fulfillment failed for order ' . $order->invoice_no . ': ' . $e->getMessage());
            return false;
        }
    }
}