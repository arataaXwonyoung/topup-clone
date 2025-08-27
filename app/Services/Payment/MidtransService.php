<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected string $baseUrl;
    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = config('payment.midtrans.server_key');
        $this->clientKey = config('payment.midtrans.client_key');
        $this->isProduction = config('payment.midtrans.is_production', false);
        $this->baseUrl = $this->isProduction 
            ? 'https://api.midtrans.com/v2' 
            : 'https://api.sandbox.midtrans.com/v2';
    }

    /**
     * Create payment transaction
     */
    public function createTransaction(Order $order, array $options = []): array
    {
        try {
            $payload = $this->buildPayload($order, $options);
            
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':')
            ])->post($this->baseUrl . '/charge', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Store payment record
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'provider' => 'midtrans',
                    'external_id' => $data['order_id'],
                    'method' => $payload['payment_type'],
                    'channel' => $options['channel'] ?? null,
                    'amount' => $order->total,
                    'fee' => $order->fee,
                    'status' => 'pending',
                    'payment_url' => $data['redirect_url'] ?? null,
                    'va_number' => $data['va_numbers'][0]['va_number'] ?? null,
                    'qr_code' => $data['qr_string'] ?? null,
                    'expires_at' => now()->addHours(3),
                    'response_data' => $data
                ]);

                Log::info("Midtrans payment created", [
                    'order_id' => $order->id,
                    'external_id' => $data['order_id'],
                    'payment_type' => $payload['payment_type']
                ]);

                return [
                    'success' => true,
                    'payment_id' => $payment->id,
                    'external_id' => $data['order_id'],
                    'payment_url' => $data['redirect_url'] ?? null,
                    'va_number' => $data['va_numbers'][0]['va_number'] ?? null,
                    'qr_code' => $data['qr_string'] ?? null,
                    'instructions' => $this->getPaymentInstructions($data),
                ];
            }

            Log::error("Midtrans API error", [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Payment gateway error: ' . ($response->json()['error_messages'][0] ?? 'Unknown error')
            ];

        } catch (\Exception $e) {
            Log::error("Midtrans payment creation failed", [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Payment system temporarily unavailable'
            ];
        }
    }

    /**
     * Build payment payload
     */
    protected function buildPayload(Order $order, array $options): array
    {
        $payload = [
            'payment_type' => $this->mapPaymentType($options['method'] ?? 'qris'),
            'transaction_details' => [
                'order_id' => $order->invoice_no,
                'gross_amount' => (int) $order->total
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->email,
                'phone' => $order->whatsapp,
            ],
            'item_details' => [
                [
                    'id' => $order->denomination->id,
                    'price' => (int) $order->denomination->price,
                    'quantity' => $order->quantity,
                    'name' => $order->game->name . ' - ' . $order->denomination->name,
                    'category' => 'Gaming',
                ]
            ]
        ];

        // Add method-specific configurations
        switch ($payload['payment_type']) {
            case 'bank_transfer':
                $payload['bank_transfer'] = [
                    'bank' => $options['bank'] ?? 'bca'
                ];
                break;

            case 'echannel':
                $payload['echannel'] = [
                    'bill_info1' => 'Payment For:',
                    'bill_info2' => $order->game->name
                ];
                break;

            case 'gopay':
                $payload['gopay'] = [
                    'enable_callback' => true,
                    'callback_url' => route('payment.callback', ['provider' => 'midtrans'])
                ];
                break;

            case 'qris':
                $payload['qris'] = [
                    'acquirer' => 'gopay'
                ];
                break;
        }

        return $payload;
    }

    /**
     * Map payment method to Midtrans type
     */
    protected function mapPaymentType(string $method): string
    {
        return match($method) {
            'va_bca' => 'bank_transfer',
            'va_bni' => 'bank_transfer', 
            'va_bri' => 'bank_transfer',
            'va_mandiri' => 'echannel',
            'gopay' => 'gopay',
            'ovo', 'dana', 'linkaja' => 'qris',
            'qris' => 'qris',
            default => 'qris'
        };
    }

    /**
     * Handle webhook notification
     */
    public function handleWebhook(array $payload): array
    {
        try {
            // Verify signature
            if (!$this->verifySignature($payload)) {
                Log::warning("Invalid Midtrans webhook signature", $payload);
                return ['success' => false, 'message' => 'Invalid signature'];
            }

            $orderId = $payload['order_id'];
            $transactionStatus = $payload['transaction_status'];
            $fraudStatus = $payload['fraud_status'] ?? null;

            // Find order
            $order = Order::where('invoice_no', $orderId)->first();
            if (!$order) {
                Log::warning("Order not found for webhook", ['order_id' => $orderId]);
                return ['success' => false, 'message' => 'Order not found'];
            }

            // Update payment status
            $payment = $order->payment;
            if ($payment) {
                $payment->update([
                    'status' => $this->mapTransactionStatus($transactionStatus, $fraudStatus),
                    'response_data' => $payload
                ]);
            }

            // Update order status based on transaction status
            switch ($transactionStatus) {
                case 'capture':
                case 'settlement':
                    if ($fraudStatus === 'accept' || $fraudStatus === null) {
                        $order->update(['status' => 'PAID', 'paid_at' => now()]);
                        
                        // Trigger fulfillment for digital products
                        if ($order->game->is_digital ?? true) {
                            dispatch(function() use ($order) {
                                app(OrderService::class)->fulfillOrder($order);
                            })->delay(now()->addSeconds(30));
                        }
                    }
                    break;

                case 'pending':
                    $order->update(['status' => 'PENDING']);
                    break;

                case 'deny':
                case 'cancel':
                case 'expire':
                case 'failure':
                    $order->update(['status' => 'FAILED']);
                    break;
            }

            Log::info("Midtrans webhook processed", [
                'order_id' => $orderId,
                'status' => $transactionStatus,
                'order_status' => $order->status
            ]);

            return ['success' => true, 'message' => 'Webhook processed'];

        } catch (\Exception $e) {
            Log::error("Midtrans webhook processing failed", [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return ['success' => false, 'message' => 'Webhook processing failed'];
        }
    }

    /**
     * Verify webhook signature
     */
    protected function verifySignature(array $payload): bool
    {
        $signatureKey = $payload['signature_key'] ?? '';
        
        $orderId = $payload['order_id'];
        $statusCode = $payload['status_code'];
        $grossAmount = $payload['gross_amount'];
        
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        
        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Map transaction status to internal status
     */
    protected function mapTransactionStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            return $fraudStatus === 'accept' || $fraudStatus === null ? 'paid' : 'failed';
        }

        return match($transactionStatus) {
            'pending' => 'pending',
            'deny', 'cancel', 'expire', 'failure' => 'failed',
            default => 'pending'
        };
    }

    /**
     * Get payment instructions for user
     */
    protected function getPaymentInstructions(array $responseData): array
    {
        $instructions = [];

        if (isset($responseData['va_numbers'])) {
            $vaNumber = $responseData['va_numbers'][0]['va_number'];
            $bank = strtoupper($responseData['va_numbers'][0]['bank']);
            
            $instructions = [
                'type' => 'bank_transfer',
                'bank' => $bank,
                'va_number' => $vaNumber,
                'steps' => $this->getBankTransferSteps($bank, $vaNumber)
            ];
        } elseif (isset($responseData['qr_string'])) {
            $instructions = [
                'type' => 'qris',
                'qr_code' => $responseData['qr_string'],
                'steps' => [
                    'Buka aplikasi e-wallet atau mobile banking',
                    'Pilih menu Scan QR Code',
                    'Scan QR Code yang tersedia',
                    'Konfirmasi pembayaran',
                    'Pembayaran selesai'
                ]
            ];
        }

        return $instructions;
    }

    /**
     * Get bank transfer steps
     */
    protected function getBankTransferSteps(string $bank, string $vaNumber): array
    {
        $baseSteps = [
            "Login ke {$bank} mobile banking atau ATM",
            "Pilih menu Transfer/Bayar",
            "Pilih Virtual Account",
            "Masukkan nomor VA: {$vaNumber}",
            "Konfirmasi pembayaran",
            "Simpan bukti transaksi"
        ];

        return $baseSteps;
    }

    /**
     * Check transaction status
     */
    public function checkStatus(Order $order): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':')
            ])->get($this->baseUrl . '/' . $order->invoice_no . '/status');

            if ($response->successful()) {
                return $response->json();
            }

            return ['status_code' => '404', 'status_message' => 'Transaction not found'];

        } catch (\Exception $e) {
            Log::error("Status check failed for order {$order->invoice_no}: {$e->getMessage()}");
            return ['status_code' => '500', 'status_message' => 'Status check failed'];
        }
    }
}