<?php

namespace App\Services\Payment\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Http;

class TripayDriver implements PaymentService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $privateKey;
    protected string $merchantCode;

    public function __construct()
    {
        $this->apiUrl = config('services.tripay.is_production', false) 
            ? 'https://tripay.co.id/api'
            : 'https://tripay.co.id/api-sandbox';
        
        $this->apiKey = config('services.tripay.api_key');
        $this->privateKey = config('services.tripay.private_key');
        $this->merchantCode = config('services.tripay.merchant_code');
    }

    public function createCharge(Order $order, array $options = []): Payment
    {
        $method = $options['method'] ?? 'QRIS';
        $reference = 'TP-' . $order->invoice_no;

        try {
            $channelCode = $this->getChannelCode($method, $options['channel'] ?? null);
            
            $signature = hash_hmac('sha256', 
                $this->merchantCode . $reference . (int)$order->total,
                $this->privateKey
            );

            $payload = [
                'method' => $channelCode,
                'merchant_ref' => $reference,
                'amount' => (int) $order->total,
                'customer_name' => substr($order->username ?? 'Customer', 0, 50),
                'customer_email' => $order->email,
                'customer_phone' => $order->whatsapp,
                'order_items' => [
                    [
                        'sku' => $order->denomination->sku ?? 'ITEM-' . $order->denomination_id,
                        'name' => $order->game->name . ' - ' . $order->denomination->name,
                        'price' => (int) $order->total,
                        'quantity' => 1,
                    ]
                ],
                'return_url' => route('invoices.show', $order->invoice_no),
                'expired_time' => (int) now()->addHours(3)->timestamp,
                'signature' => $signature,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl . '/transaction/create', $payload);

            if (!$response->successful()) {
                throw new \Exception('Tripay API error: ' . $response->body());
            }

            $data = $response->json()['data'];

            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'tripay',
                'method' => $method,
                'channel' => $options['channel'] ?? $channelCode,
                'reference' => $reference,
                'external_id' => $data['reference'],
                'status' => 'PENDING',
                'amount' => $order->total,
                'fee' => $data['fee_merchant'] ?? 0,
                'expires_at' => now()->addHours(3),
                'payload' => $data,
            ]);

            // Set payment details based on method
            if ($method === 'QRIS' && isset($data['qr_string'])) {
                $payment->qris_string = $data['qr_string'];
            } elseif ($method === 'VA' && isset($data['pay_code'])) {
                $payment->va_number = $data['pay_code'];
            } elseif ($method === 'EWALLET' && isset($data['checkout_url'])) {
                $payment->checkout_url = $data['checkout_url'];
            }

            $payment->save();
            return $payment;

        } catch (\Exception $e) {
            throw new \Exception('Failed to create Tripay payment: ' . $e->getMessage());
        }
    }

    public function checkStatus(Payment $payment): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->apiUrl . '/transaction/detail', [
                'reference' => $payment->external_id,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to check status');
            }

            $data = $response->json()['data'];
            
            return [
                'status' => $this->mapStatus($data['status']),
                'paid_at' => $data['paid_at'] ?? null,
                'raw' => $data,
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
        $reference = $payload['merchant_ref'] ?? null;
        $status = $payload['status'] ?? null;
        
        if (!$reference) {
            return false;
        }

        $payment = Payment::where('reference', $reference)->first();
        
        if (!$payment) {
            return false;
        }

        $mappedStatus = $this->mapStatus($status);
        
        if ($mappedStatus === 'PAID' && $payment->status !== 'PAID') {
            $payment->markAsPaid();
            $payment->order->markAsPaid();
            
            dispatch(new \App\Jobs\AutoFulfillmentJob($payment->order));
        } elseif ($mappedStatus === 'EXPIRED') {
            $payment->markAsExpired();
            $payment->order->markAsExpired();
        } elseif ($mappedStatus === 'FAILED') {
            $payment->update(['status' => 'FAILED']);
            $payment->order->update(['status' => 'FAILED']);
        }

        return true;
    }

    public function verifyWebhookSignature(string $signature, array $payload): bool
    {
        $json = json_encode($payload);
        $expectedSignature = hash_hmac('sha256', $json, $this->privateKey);
        
        return hash_equals($expectedSignature, $signature);
    }

    public function cancelPayment(Payment $payment): bool
    {
        // Tripay doesn't support cancellation, just mark as failed locally
        $payment->update(['status' => 'FAILED']);
        return true;
    }

    protected function getChannelCode(string $method, ?string $channel): string
    {
        $channels = [
            'QRIS' => 'QRIS',
            'VA' => [
                'bca' => 'BCAVA',
                'bni' => 'BNIVA',
                'bri' => 'BRIVA',
                'mandiri' => 'MANDIRIVA',
                'permata' => 'PERMATAVA',
                'cimb' => 'CIMBVA',
                'maybank' => 'MYBVA',
            ],
            'EWALLET' => [
                'ovo' => 'OVO',
                'dana' => 'DANA',
                'shopeepay' => 'SHOPEEPAY',
                'linkaja' => 'LINKAJA',
            ],
            'CONVENIENCE' => [
                'alfamart' => 'ALFAMART',
                'indomaret' => 'INDOMARET',
            ],
        ];

        if ($method === 'QRIS') {
            return $channels['QRIS'];
        }

        if (isset($channels[$method][$channel])) {
            return $channels[$method][$channel];
        }

        // Default channels
        return match ($method) {
            'VA' => 'BCAVA',
            'EWALLET' => 'OVO',
            'CONVENIENCE' => 'ALFAMART',
            default => 'QRIS',
        };
    }

    protected function mapStatus(string $tripayStatus): string
    {
        return match (strtoupper($tripayStatus)) {
            'PAID' => 'PAID',
            'UNPAID' => 'PENDING',
            'REFUND' => 'REFUNDED',
            'EXPIRED' => 'EXPIRED',
            'FAILED' => 'FAILED',
            default => 'PENDING',
        };
    }
}