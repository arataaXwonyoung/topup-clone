<?php

namespace App\Services\Payment\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\QRCode\QRCodeApi;
use Xendit\QRCode\CreateQRCodeRequest;
use Xendit\VirtualAccounts\VirtualAccountApi;
use Xendit\VirtualAccounts\CreateVirtualAccountRequest;

class XenditDriver implements PaymentService
{
    protected InvoiceApi $invoiceApi;
    protected QRCodeApi $qrCodeApi;
    protected VirtualAccountApi $vaApi;

    public function __construct()
    {
        Configuration::setXenditKey(config('services.xendit.secret_key'));
        
        $this->invoiceApi = new InvoiceApi();
        $this->qrCodeApi = new QRCodeApi();
        $this->vaApi = new VirtualAccountApi();
    }

    public function createCharge(Order $order, array $options = []): Payment
    {
        $method = $options['method'] ?? 'QRIS';
        $reference = 'XN-' . $order->invoice_no;

        try {
            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'xendit',
                'method' => $method,
                'channel' => $options['channel'] ?? null,
                'reference' => $reference,
                'status' => 'PENDING',
                'amount' => $order->total,
                'expires_at' => now()->addHours(3),
            ]);

            if ($method === 'QRIS') {
                $qrCode = $this->createQRIS($order, $reference);
                $payment->update([
                    'external_id' => $qrCode['id'],
                    'qris_string' => $qrCode['qr_string'],
                ]);
            } elseif ($method === 'VA') {
                $va = $this->createVirtualAccount($order, $reference, $options['channel'] ?? 'BCA');
                $payment->update([
                    'external_id' => $va['id'],
                    'va_number' => $va['account_number'],
                ]);
            } elseif ($method === 'EWALLET') {
                $invoice = $this->createEwalletInvoice($order, $reference, $options['channel'] ?? 'OVO');
                $payment->update([
                    'external_id' => $invoice['id'],
                    'checkout_url' => $invoice['invoice_url'],
                ]);
            }

            $payment->save();
            return $payment;

        } catch (\Exception $e) {
            throw new \Exception('Failed to create Xendit payment: ' . $e->getMessage());
        }
    }

    protected function createQRIS($order, $reference): array
    {
        try {
            $request = new CreateQRCodeRequest([
                'reference_id' => $reference,
                'type' => 'DYNAMIC',
                'currency' => 'IDR',
                'amount' => (float) $order->total,
                'expires_at' => now()->addHours(3)->toIso8601String(),
                'metadata' => [
                    'order_id' => $order->id,
                    'invoice_no' => $order->invoice_no,
                ],
            ]);

            $result = $this->qrCodeApi->createQRCode($request);
            
            return [
                'id' => $result['id'],
                'qr_string' => $result['qr_string'],
                'expires_at' => $result['expires_at'],
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to create QRIS: ' . $e->getMessage());
        }
    }

    protected function createVirtualAccount($order, $reference, $bankCode): array
    {
        try {
            $request = new CreateVirtualAccountRequest([
                'external_id' => $reference,
                'bank_code' => strtoupper($bankCode),
                'name' => substr($order->email, 0, 50),
                'is_closed' => true,
                'expected_amount' => (float) $order->total,
                'expiration_date' => now()->addHours(3)->toIso8601String(),
                'is_single_use' => true,
            ]);

            $result = $this->vaApi->createVirtualAccount($request);
            
            return [
                'id' => $result['id'],
                'account_number' => $result['account_number'],
                'bank_code' => $result['bank_code'],
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to create VA: ' . $e->getMessage());
        }
    }

    protected function createEwalletInvoice($order, $reference, $channel): array
    {
        try {
            $request = new CreateInvoiceRequest([
                'external_id' => $reference,
                'amount' => (float) $order->total,
                'payer_email' => $order->email,
                'description' => 'Payment for ' . $order->game->name,
                'invoice_duration' => 10800, // 3 hours in seconds
                'payment_methods' => [strtoupper($channel)],
                'currency' => 'IDR',
                'success_redirect_url' => route('invoices.show', $order->invoice_no),
                'failure_redirect_url' => route('invoices.show', $order->invoice_no),
                'metadata' => [
                    'order_id' => $order->id,
                    'invoice_no' => $order->invoice_no,
                ],
            ]);

            $result = $this->invoiceApi->createInvoice($request);
            
            return [
                'id' => $result['id'],
                'invoice_url' => $result['invoice_url'],
                'expiry_date' => $result['expiry_date'],
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to create e-wallet invoice: ' . $e->getMessage());
        }
    }

    public function checkStatus(Payment $payment): array
    {
        try {
            $status = 'PENDING';
            $paidAt = null;

            if ($payment->method === 'QRIS') {
                $result = $this->qrCodeApi->getQRCodeById($payment->external_id);
                $status = $this->mapQRStatus($result['status']);
            } elseif ($payment->method === 'VA') {
                $result = $this->vaApi->getVirtualAccountById($payment->external_id);
                $status = $result['status'] === 'ACTIVE' ? 'PENDING' : 'PAID';
            } else {
                $result = $this->invoiceApi->getInvoiceById($payment->external_id);
                $status = $this->mapInvoiceStatus($result['status']);
                $paidAt = $result['paid_at'] ?? null;
            }

            return [
                'status' => $status,
                'paid_at' => $paidAt,
                'raw' => $result ?? [],
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
        $externalId = $payload['external_id'] ?? null;
        $status = $payload['status'] ?? null;
        
        if (!$externalId) {
            return false;
        }

        $payment = Payment::where('reference', $externalId)
            ->orWhere('external_id', $payload['id'] ?? null)
            ->first();
        
        if (!$payment) {
            return false;
        }

        $mappedStatus = $this->mapWebhookStatus($status);
        
        if ($mappedStatus === 'PAID' && $payment->status !== 'PAID') {
            $payment->markAsPaid();
            $payment->order->markAsPaid();
            
            dispatch(new \App\Jobs\AutoFulfillmentJob($payment->order));
        } elseif ($mappedStatus === 'EXPIRED') {
            $payment->markAsExpired();
            $payment->order->markAsExpired();
        }

        return true;
    }

    public function verifyWebhookSignature(string $signature, array $payload): bool
    {
        $webhookToken = config('services.xendit.callback_token');
        
        if (!$webhookToken) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', json_encode($payload), $webhookToken);
        
        return hash_equals($expectedSignature, $signature);
    }

    public function cancelPayment(Payment $payment): bool
    {
        try {
            if ($payment->method === 'VA') {
                $this->vaApi->updateVirtualAccount($payment->external_id, [
                    'is_closed' => false,
                    'expiration_date' => now()->toIso8601String(),
                ]);
            } elseif (in_array($payment->method, ['QRIS', 'EWALLET'])) {
                $this->invoiceApi->expireInvoice($payment->external_id);
            }
            
            $payment->update(['status' => 'FAILED']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function mapQRStatus(string $xenditStatus): string
    {
        return match ($xenditStatus) {
            'ACTIVE' => 'PENDING',
            'COMPLETED' => 'PAID',
            'EXPIRED', 'INACTIVE' => 'EXPIRED',
            default => 'PENDING',
        };
    }

    protected function mapInvoiceStatus(string $xenditStatus): string
    {
        return match ($xenditStatus) {
            'PENDING' => 'PENDING',
            'PAID', 'SETTLED' => 'PAID',
            'EXPIRED' => 'EXPIRED',
            default => 'PENDING',
        };
    }

    protected function mapWebhookStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'PAID', 'SETTLED', 'COMPLETED', 'ACTIVE' => 'PAID',
            'EXPIRED', 'INACTIVE' => 'EXPIRED',
            default => 'PENDING',
        };
    }
}