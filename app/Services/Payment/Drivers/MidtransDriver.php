<?php

namespace App\Services\Payment\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransDriver implements PaymentService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createCharge(Order $order, array $options = []): Payment
    {
        $method = $options['method'] ?? 'QRIS';
        $reference = 'MT-' . $order->invoice_no;

        $params = [
            'transaction_details' => [
                'order_id' => $reference,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->username ?? 'Customer',
                'email' => $order->email,
                'phone' => $order->whatsapp,
            ],
            'item_details' => [
                [
                    'id' => $order->denomination->sku ?? $order->denomination_id,
                    'price' => (int) $order->total,
                    'quantity' => 1,
                    'name' => $order->game->name . ' - ' . $order->denomination->name,
                ],
            ],
            'expiry' => [
                'unit' => 'hour',
                'duration' => 3,
            ],
        ];

        // Set payment method
        if ($method === 'QRIS') {
            $params['enabled_payments'] = ['gopay', 'shopeepay', 'qris'];
        } elseif ($method === 'VA') {
            $params['enabled_payments'] = ['bank_transfer'];
            $params['bank_transfer'] = [
                'bank' => $options['channel'] ?? 'bca',
            ];
        } elseif ($method === 'EWALLET') {
            $params['enabled_payments'] = ['gopay', 'shopeepay'];
        }

        try {
            $snapToken = Snap::getSnapToken($params);
            
            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'midtrans',
                'method' => $method,
                'channel' => $options['channel'] ?? null,
                'reference' => $reference,
                'external_id' => $snapToken,
                'checkout_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken,
                'status' => 'PENDING',
                'amount' => $order->total,
                'payload' => $params,
                'expires_at' => now()->addHours(3),
            ]);

            // Get payment details
            if ($method === 'QRIS') {
                // In production, you would get actual QRIS string from Midtrans Core API
                $payment->qris_string = $this->generateMockQRIS();
            } elseif ($method === 'VA') {
                $payment->va_number = $this->generateVANumber($options['channel'] ?? 'bca');
            }

            $payment->save();

            return $payment;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create payment: ' . $e->getMessage());
        }
    }

    public function checkStatus(Payment $payment): array
    {
        try {
            $status = Transaction::status($payment->reference);
            
            return [
                'status' => $this->mapStatus($status->transaction_status),
                'paid_at' => $status->transaction_status === 'settlement' 
                    ? now() : null,
                'raw' => (array) $status,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'PENDING',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function handleWebhook(array $payload): bool
    {
        $orderId = $payload['order_id'] ?? null;
        $status = $payload['transaction_status'] ?? null;
        
        if (!$orderId || !$status) {
            return false;
        }

        $payment = Payment::where('reference', $orderId)->first();
        
        if (!$payment) {
            return false;
        }

        $mappedStatus = $this->mapStatus($status);
        
        if ($mappedStatus === 'PAID' && $payment->status !== 'PAID') {
            $payment->markAsPaid();
            $payment->order->markAsPaid();
            
            // Trigger fulfillment job
            dispatch(new \App\Jobs\AutoFulfillmentJob($payment->order));
        } elseif ($mappedStatus === 'EXPIRED') {
            $payment->markAsExpired();
            $payment->order->markAsExpired();
        }

        return true;
    }

    public function verifyWebhookSignature(string $signature, array $payload): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = config('services.midtrans.server_key');
        
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        return $signature === $expectedSignature;
    }

    public function cancelPayment(Payment $payment): bool
    {
        try {
            Transaction::cancel($payment->reference);
            $payment->update(['status' => 'FAILED']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function mapStatus(string $midtransStatus): string
    {
        return match ($midtransStatus) {
            'capture', 'settlement' => 'PAID',
            'pending' => 'PENDING',
            'deny', 'cancel' => 'FAILED',
            'expire' => 'EXPIRED',
            'refund', 'partial_refund' => 'REFUNDED',
            default => 'PENDING',
        };
    }

    protected function generateMockQRIS(): string
    {
        // Mock QRIS string for development
        return '00020101021226680019ID.CO.SHOPEE.WWW01189360091800000000020214000000000000030315ID10210002150303UME51440014ID.CO.QRIS.WWW0215ID10210002150020303UME5204541153033605802ID5925TAKAPEDIA CLONE MERCHANT6015JAKARTA SELATAN61051234062070703A016304B7F5';
    }

    protected function generateVANumber(string $bank): string
    {
        $bankCodes = [
            'bca' => '70012',
            'bni' => '8810',
            'bri' => '88808',
            'mandiri' => '70014',
        ];
        
        $prefix = $bankCodes[$bank] ?? '99999';
        return $prefix . rand(10000000, 99999999);
    }
}