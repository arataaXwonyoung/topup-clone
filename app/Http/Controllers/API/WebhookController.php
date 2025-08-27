<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentManagerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected PaymentManagerService $paymentManager;

    public function __construct(PaymentManagerService $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * Handle payment gateway webhooks
     * POST /api/payments/webhook
     */
    public function handlePaymentWebhook(Request $request): JsonResponse
    {
        try {
            // Log incoming webhook for debugging
            Log::info('Payment webhook received', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'raw_body' => $request->getContent(),
                'ip' => $request->ip(),
            ]);

            // Determine payment provider from headers or body
            $provider = $this->detectPaymentProvider($request);
            
            if (!$provider) {
                Log::warning('Unknown payment provider webhook', [
                    'headers' => $request->headers->all(),
                    'body' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unknown payment provider'
                ], 400);
            }

            // Get payment service instance
            $paymentService = $this->paymentManager->getProvider($provider);

            // Verify webhook signature
            if (!$paymentService->verifyWebhookSignature($request)) {
                Log::error('Invalid webhook signature', [
                    'provider' => $provider,
                    'headers' => $request->headers->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 401);
            }

            // Parse webhook data
            $webhookData = $paymentService->parseWebhookData($request);

            if (!$webhookData) {
                Log::error('Failed to parse webhook data', [
                    'provider' => $provider,
                    'body' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook data'
                ], 400);
            }

            // Find payment record
            $payment = Payment::where('payment_id', $webhookData['payment_id'])
                ->orWhere('gateway_transaction_id', $webhookData['transaction_id'] ?? '')
                ->first();

            if (!$payment) {
                Log::error('Payment not found for webhook', [
                    'provider' => $provider,
                    'payment_id' => $webhookData['payment_id'] ?? 'N/A',
                    'transaction_id' => $webhookData['transaction_id'] ?? 'N/A',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Update payment status
            $this->updatePaymentFromWebhook($payment, $webhookData);

            // Update order status
            $this->updateOrderFromPayment($payment);

            Log::info('Webhook processed successfully', [
                'provider' => $provider,
                'payment_id' => $payment->payment_id,
                'order_id' => $payment->order_id,
                'status' => $webhookData['status'],
            ]);

            // Return appropriate response for each provider
            return $this->getWebhookResponse($provider);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Detect payment provider from request
     */
    protected function detectPaymentProvider(Request $request): ?string
    {
        // Check User-Agent header
        $userAgent = $request->userAgent();
        if (str_contains(strtolower($userAgent), 'midtrans')) {
            return 'midtrans';
        }

        // Check custom headers
        if ($request->hasHeader('X-Callback-Token')) {
            return 'midtrans';
        }

        if ($request->hasHeader('X-CALLBACK-TOKEN')) {
            return 'xendit';
        }

        if ($request->hasHeader('X-Tripay-Signature')) {
            return 'tripay';
        }

        // Check body for provider-specific fields
        $body = $request->all();
        
        if (isset($body['transaction_status']) || isset($body['order_id'])) {
            return 'midtrans';
        }

        if (isset($body['event']) && isset($body['data'])) {
            return 'xendit';
        }

        if (isset($body['merchant_ref']) || isset($body['reference'])) {
            return 'tripay';
        }

        return null;
    }

    /**
     * Update payment from webhook data
     */
    protected function updatePaymentFromWebhook(Payment $payment, array $webhookData): void
    {
        $updateData = [
            'status' => $webhookData['status'],
            'gateway_response' => json_encode($webhookData['raw_data'] ?? []),
            'updated_at' => now(),
        ];

        if (isset($webhookData['transaction_id'])) {
            $updateData['gateway_transaction_id'] = $webhookData['transaction_id'];
        }

        if (isset($webhookData['reference'])) {
            $updateData['gateway_reference'] = $webhookData['reference'];
        }

        if ($webhookData['status'] === 'paid' && !$payment->paid_at) {
            $updateData['paid_at'] = $webhookData['paid_at'] ?? now();
        }

        if ($webhookData['status'] === 'failed') {
            $updateData['notes'] = $webhookData['failure_reason'] ?? 'Payment failed';
        }

        if (isset($webhookData['fee'])) {
            $updateData['fee'] = $webhookData['fee'];
            $updateData['net_amount'] = $payment->amount - $webhookData['fee'];
        }

        $payment->update($updateData);
    }

    /**
     * Update order status based on payment
     */
    protected function updateOrderFromPayment(Payment $payment): void
    {
        $order = $payment->order;
        if (!$order) return;

        switch ($payment->status) {
            case 'paid':
                if ($order->status === 'PENDING') {
                    $order->update([
                        'status' => 'PAID',
                        'paid_at' => $payment->paid_at,
                    ]);

                    // Trigger order fulfillment
                    $this->triggerOrderFulfillment($order);
                }
                break;

            case 'failed':
            case 'expired':
                if (in_array($order->status, ['PENDING', 'PROCESSING'])) {
                    $order->update([
                        'status' => strtoupper($payment->status),
                    ]);
                }
                break;

            case 'refunded':
                $order->update([
                    'status' => 'REFUNDED',
                    'notes' => ($order->notes ?? '') . "\nPayment refunded at " . now(),
                ]);
                break;
        }
    }

    /**
     * Trigger order fulfillment process
     */
    protected function triggerOrderFulfillment(Order $order): void
    {
        try {
            // Dispatch fulfillment job
            \App\Jobs\AutoFulfillmentJob::dispatch($order)
                ->delay(now()->addMinutes(1));

            Log::info('Order fulfillment job dispatched', [
                'order_id' => $order->id,
                'invoice_number' => $order->invoice_number,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to dispatch fulfillment job', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get appropriate webhook response for each provider
     */
    protected function getWebhookResponse(string $provider): JsonResponse
    {
        return match ($provider) {
            'midtrans' => response()->json(['status' => 'ok']),
            'xendit' => response()->json(['received' => true]),
            'tripay' => response()->json(['success' => true]),
            default => response()->json(['success' => true]),
        };
    }
}