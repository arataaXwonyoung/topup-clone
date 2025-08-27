<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\DigiflazzService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTopupToDigiflazz implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 2;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300, 900]; // 1 minute, 5 minutes, 15 minutes
    }

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(DigiflazzService $digiflazzService): void
    {
        try {
            Log::info('Starting Digiflazz topup job', [
                'order_id' => $this->order->id,
                'invoice_number' => $this->order->invoice_number,
                'attempt' => $this->attempts(),
            ]);

            // Validate order status
            if (!in_array($this->order->status, ['PAID', 'PROCESSING'])) {
                Log::warning('Order status not eligible for topup', [
                    'order_id' => $this->order->id,
                    'status' => $this->order->status,
                ]);
                return;
            }

            // Get denomination with Digiflazz SKU
            $denomination = $this->order->denomination;
            if (!$denomination || !$denomination->sku) {
                Log::error('Order denomination has no Digiflazz SKU', [
                    'order_id' => $this->order->id,
                    'denomination_id' => $this->order->denomination_id,
                ]);
                
                $this->failOrder('Denomination configuration error');
                return;
            }

            // Update order status to processing
            $this->order->update([
                'status' => 'PROCESSING',
                'notes' => ($this->order->notes ?? '') . "\nDigiflazz topup initiated at " . now(),
            ]);

            // Prepare customer data
            $customerId = $this->order->player_id;
            $additionalData = [];
            
            if ($this->order->server_id) {
                $additionalData['server_id'] = $this->order->server_id;
            }

            // Generate reference ID
            $refId = $this->order->invoice_number . '-' . now()->timestamp;

            Log::info('Sending topup request to Digiflazz', [
                'order_id' => $this->order->id,
                'ref_id' => $refId,
                'sku' => $denomination->sku,
                'customer_id' => $customerId,
                'additional_data' => $additionalData,
            ]);

            // Send topup request
            $result = $digiflazzService->topup(
                $refId,
                $denomination->sku,
                $customerId,
                $additionalData
            );

            if ($result['success']) {
                $this->handleTopupSuccess($result, $refId);
            } else {
                $this->handleTopupFailure($result, $refId);
            }

        } catch (\Exception $e) {
            Log::error('Digiflazz topup job exception', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);

            // If this is the final attempt, mark order as failed
            if ($this->attempts() >= $this->tries) {
                $this->failOrder('Topup service error after ' . $this->tries . ' attempts: ' . $e->getMessage());
            }

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle successful topup response
     */
    protected function handleTopupSuccess(array $result, string $refId): void
    {
        Log::info('Digiflazz topup successful', [
            'order_id' => $this->order->id,
            'ref_id' => $refId,
            'trx_id' => $result['trx_id'] ?? null,
            'status' => $result['status'] ?? 'Unknown',
        ]);

        $updateData = [
            'notes' => ($this->order->notes ?? '') . "\nDigiflazz Response: " . ($result['message'] ?? 'Success'),
        ];

        // Store transaction details in metadata
        $metadata = $this->order->metadata ?? [];
        $metadata['digiflazz'] = [
            'ref_id' => $refId,
            'trx_id' => $result['trx_id'] ?? null,
            'status' => $result['status'] ?? 'Unknown',
            'sn' => $result['sn'] ?? null,
            'price' => $result['price'] ?? null,
            'processed_at' => now()->toISOString(),
            'attempts' => $this->attempts(),
        ];
        $updateData['metadata'] = $metadata;

        // Update order status based on Digiflazz response
        $digiflazzStatus = strtolower($result['status'] ?? '');
        
        if (in_array($digiflazzStatus, ['success', 'sukses', 'delivered'])) {
            $updateData['status'] = 'DELIVERED';
            $updateData['delivered_at'] = now();
            
            Log::info('Order marked as delivered', [
                'order_id' => $this->order->id,
                'digiflazz_status' => $result['status'],
            ]);
            
        } elseif (in_array($digiflazzStatus, ['pending', 'process'])) {
            // Keep status as PROCESSING, will be updated via webhook
            Log::info('Order kept in processing state, awaiting webhook', [
                'order_id' => $this->order->id,
                'digiflazz_status' => $result['status'],
            ]);
            
        } else {
            // Unexpected status, log for investigation
            Log::warning('Unexpected Digiflazz status', [
                'order_id' => $this->order->id,
                'digiflazz_status' => $result['status'],
                'full_response' => $result,
            ]);
        }

        $this->order->update($updateData);

        // Schedule status check job for pending transactions
        if (in_array($digiflazzStatus, ['pending', 'process'])) {
            \App\Jobs\CheckDigiflazzTransactionStatus::dispatch($this->order, $refId)
                ->delay(now()->addMinutes(5));
        }
    }

    /**
     * Handle failed topup response
     */
    protected function handleTopupFailure(array $result, string $refId): void
    {
        $errorMessage = $result['message'] ?? 'Unknown error';
        
        Log::error('Digiflazz topup failed', [
            'order_id' => $this->order->id,
            'ref_id' => $refId,
            'error' => $errorMessage,
            'full_response' => $result,
            'attempt' => $this->attempts(),
        ]);

        // Store failure details in metadata
        $metadata = $this->order->metadata ?? [];
        $metadata['digiflazz_failures'] = $metadata['digiflazz_failures'] ?? [];
        $metadata['digiflazz_failures'][] = [
            'ref_id' => $refId,
            'error' => $errorMessage,
            'attempt' => $this->attempts(),
            'failed_at' => now()->toISOString(),
        ];

        // Determine if error is retryable
        $retryableErrors = [
            'insufficient balance',
            'service temporarily unavailable',
            'timeout',
            'connection',
            'server error',
        ];

        $isRetryable = false;
        foreach ($retryableErrors as $retryableError) {
            if (stripos($errorMessage, $retryableError) !== false) {
                $isRetryable = true;
                break;
            }
        }

        // If this is the final attempt or error is not retryable
        if ($this->attempts() >= $this->tries || !$isRetryable) {
            $this->failOrder('Digiflazz topup failed: ' . $errorMessage, $metadata);
            return;
        }

        // Update order with failure info but keep in PROCESSING for retry
        $this->order->update([
            'metadata' => $metadata,
            'notes' => ($this->order->notes ?? '') . "\nTopup attempt {$this->attempts()} failed: {$errorMessage}",
        ]);

        // Throw exception to trigger retry
        throw new \Exception("Digiflazz topup failed (attempt {$this->attempts()}): " . $errorMessage);
    }

    /**
     * Mark order as failed
     */
    protected function failOrder(string $reason, ?array $metadata = null): void
    {
        $updateData = [
            'status' => 'FAILED',
            'notes' => ($this->order->notes ?? '') . "\nOrder failed: {$reason}",
        ];

        if ($metadata) {
            $updateData['metadata'] = $metadata;
        }

        $this->order->update($updateData);

        Log::error('Order marked as failed', [
            'order_id' => $this->order->id,
            'reason' => $reason,
        ]);

        // Optionally notify customer about failure
        // \App\Jobs\SendOrderFailedNotification::dispatch($this->order, $reason);
    }

    /**
     * The job failed to process.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Digiflazz topup job permanently failed', [
            'order_id' => $this->order->id,
            'exception' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Mark order as failed if not already handled
        if (in_array($this->order->fresh()->status, ['PROCESSING', 'PAID'])) {
            $this->failOrder('Job failed permanently: ' . $exception->getMessage());
        }
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHour(); // Give up after 1 hour
    }
}