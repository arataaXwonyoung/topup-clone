<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\DigiflazzService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DigiflazzWebhookController extends Controller
{
    /**
     * Handle Digiflazz webhook
     * POST /webhooks/digiflazz
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            Log::info('Digiflazz webhook received', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'ip' => $request->ip(),
            ]);

            // Get webhook data
            $data = $request->all();

            // Validate required fields
            if (!isset($data['ref_id']) || !isset($data['status'])) {
                Log::error('Digiflazz webhook missing required fields', [
                    'data' => $data,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields'
                ], 400);
            }

            // Verify signature if provided
            if ($request->hasHeader('X-Digiflazz-Signature')) {
                $signature = $request->header('X-Digiflazz-Signature');
                $digiflazzService = app(DigiflazzService::class);

                if (!$digiflazzService->verifyWebhookSignature($data, $signature)) {
                    Log::error('Digiflazz webhook signature verification failed', [
                        'provided_signature' => $signature,
                        'data' => $data,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid signature'
                    ], 401);
                }
            }

            // Find order by reference ID
            $refId = $data['ref_id'];
            $order = $this->findOrderByRefId($refId);

            if (!$order) {
                Log::error('Order not found for Digiflazz webhook', [
                    'ref_id' => $refId,
                    'data' => $data,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Process webhook
            $this->processDigiflazzWebhook($order, $data);

            Log::info('Digiflazz webhook processed successfully', [
                'ref_id' => $refId,
                'order_id' => $order->id,
                'status' => $data['status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed'
            ]);

        } catch (\Exception $e) {
            Log::error('Digiflazz webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Find order by Digiflazz reference ID
     */
    protected function findOrderByRefId(string $refId): ?Order
    {
        // Try to extract invoice number from ref_id (format: INV-XXXXX-timestamp)
        if (preg_match('/^(INV-[A-F0-9]+)-\d+$/', $refId, $matches)) {
            $invoiceNumber = $matches[1];
            return Order::where('invoice_number', $invoiceNumber)->first();
        }

        // Also check in metadata for orders that might have stored the ref_id
        return Order::whereJsonContains('metadata->digiflazz->ref_id', $refId)->first();
    }

    /**
     * Process Digiflazz webhook data
     */
    protected function processDigiflazzWebhook(Order $order, array $data): void
    {
        $status = strtolower($data['status']);
        $metadata = $order->metadata ?? [];

        // Update Digiflazz data in metadata
        $metadata['digiflazz'] = array_merge($metadata['digiflazz'] ?? [], [
            'webhook_received_at' => now()->toISOString(),
            'webhook_status' => $status,
            'webhook_data' => $data,
        ]);

        $updateData = [
            'metadata' => $metadata,
        ];

        // Process based on status
        switch ($status) {
            case 'sukses':
            case 'success':
            case 'delivered':
                $this->handleSuccessfulWebhook($order, $data, $updateData);
                break;

            case 'gagal':
            case 'failed':
            case 'error':
                $this->handleFailedWebhook($order, $data, $updateData);
                break;

            case 'pending':
            case 'process':
                $this->handlePendingWebhook($order, $data, $updateData);
                break;

            default:
                Log::warning('Unknown Digiflazz webhook status', [
                    'order_id' => $order->id,
                    'status' => $status,
                    'data' => $data,
                ]);

                $updateData['notes'] = ($order->notes ?? '') . 
                    "\nUnknown webhook status received: {$status}";
                break;
        }

        $order->update($updateData);
    }

    /**
     * Handle successful webhook
     */
    protected function handleSuccessfulWebhook(Order $order, array $data, array &$updateData): void
    {
        Log::info('Processing successful Digiflazz webhook', [
            'order_id' => $order->id,
            'current_status' => $order->status,
        ]);

        // Only update if order is still processing
        if (in_array($order->status, ['PROCESSING', 'PAID'])) {
            $updateData['status'] = 'DELIVERED';
            $updateData['delivered_at'] = now();
            
            $notes = "Digiflazz delivery confirmed";
            if (isset($data['sn']) && !empty($data['sn'])) {
                $notes .= " - SN: {$data['sn']}";
                $updateData['metadata']['digiflazz']['sn'] = $data['sn'];
            }
            
            $updateData['notes'] = ($order->notes ?? '') . "\n{$notes}";

            Log::info('Order marked as delivered via webhook', [
                'order_id' => $order->id,
                'sn' => $data['sn'] ?? null,
            ]);
        } else {
            Log::info('Order already processed, webhook ignored', [
                'order_id' => $order->id,
                'current_status' => $order->status,
            ]);
        }
    }

    /**
     * Handle failed webhook
     */
    protected function handleFailedWebhook(Order $order, array $data, array &$updateData): void
    {
        Log::error('Processing failed Digiflazz webhook', [
            'order_id' => $order->id,
            'current_status' => $order->status,
            'error_message' => $data['message'] ?? 'Unknown error',
        ]);

        // Only update if order is still processing
        if (in_array($order->status, ['PROCESSING', 'PAID'])) {
            $updateData['status'] = 'FAILED';
            
            $errorMessage = $data['message'] ?? 'Transaction failed';
            $updateData['notes'] = ($order->notes ?? '') . 
                "\nDigiflazz transaction failed: {$errorMessage}";

            Log::info('Order marked as failed via webhook', [
                'order_id' => $order->id,
                'error' => $errorMessage,
            ]);
        }
    }

    /**
     * Handle pending webhook
     */
    protected function handlePendingWebhook(Order $order, array $data, array &$updateData): void
    {
        Log::info('Processing pending Digiflazz webhook', [
            'order_id' => $order->id,
            'current_status' => $order->status,
        ]);

        // Keep order in processing status
        $updateData['notes'] = ($order->notes ?? '') . 
            "\nDigiflazz transaction still in progress";

        // Schedule status check if not already scheduled recently
        $lastCheck = $order->metadata['digiflazz']['last_status_check']['checked_at'] ?? null;
        $shouldScheduleCheck = !$lastCheck || 
            now()->diffInMinutes($lastCheck) > 10;

        if ($shouldScheduleCheck) {
            $refId = $data['ref_id'];
            \App\Jobs\CheckDigiflazzTransactionStatus::dispatch($order, $refId)
                ->delay(now()->addMinutes(2));
                
            Log::info('Scheduled status check job for pending webhook', [
                'order_id' => $order->id,
                'ref_id' => $refId,
            ]);
        }
    }
}