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

class CheckDigiflazzTransactionStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;
    protected string $refId;
    
    public int $tries = 5;
    public int $maxExceptions = 3;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [300, 600, 1200, 2400]; // 5min, 10min, 20min, 40min
    }

    public function __construct(Order $order, string $refId)
    {
        $this->order = $order;
        $this->refId = $refId;
    }

    public function handle(DigiflazzService $digiflazzService): void
    {
        try {
            Log::info('Checking Digiflazz transaction status', [
                'order_id' => $this->order->id,
                'ref_id' => $this->refId,
                'attempt' => $this->attempts(),
            ]);

            // Skip if order is no longer in processing
            if (!in_array($this->order->fresh()->status, ['PROCESSING'])) {
                Log::info('Order no longer in processing, skipping status check', [
                    'order_id' => $this->order->id,
                    'current_status' => $this->order->status,
                ]);
                return;
            }

            $result = $digiflazzService->checkTransactionStatus($this->refId);

            if ($result['success'] && isset($result['data'])) {
                $this->handleStatusResponse($result['data']);
            } else {
                Log::warning('Failed to check transaction status', [
                    'order_id' => $this->order->id,
                    'ref_id' => $this->refId,
                    'error' => $result['message'] ?? 'Unknown error',
                ]);

                // Retry unless final attempt
                if ($this->attempts() < $this->tries) {
                    throw new \Exception('Status check failed: ' . ($result['message'] ?? 'Unknown error'));
                }
            }

        } catch (\Exception $e) {
            Log::error('Status check job exception', [
                'order_id' => $this->order->id,
                'ref_id' => $this->refId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() >= $this->tries) {
                Log::warning('Status check job failed permanently', [
                    'order_id' => $this->order->id,
                    'ref_id' => $this->refId,
                ]);
            }

            throw $e;
        }
    }

    protected function handleStatusResponse(array $data): void
    {
        $status = strtolower($data['status'] ?? '');
        
        Log::info('Transaction status received', [
            'order_id' => $this->order->id,
            'ref_id' => $this->refId,
            'status' => $status,
            'data' => $data,
        ]);

        $updateData = [];
        $metadata = $this->order->metadata ?? [];

        // Update metadata with latest status
        $metadata['digiflazz']['last_status_check'] = [
            'status' => $status,
            'checked_at' => now()->toISOString(),
            'attempt' => $this->attempts(),
            'data' => $data,
        ];

        if (in_array($status, ['success', 'sukses', 'delivered'])) {
            // Transaction successful
            $updateData['status'] = 'DELIVERED';
            $updateData['delivered_at'] = now();
            
            if (isset($data['sn'])) {
                $metadata['digiflazz']['sn'] = $data['sn'];
            }
            
            Log::info('Order delivered via status check', [
                'order_id' => $this->order->id,
                'ref_id' => $this->refId,
            ]);

        } elseif (in_array($status, ['failed', 'gagal', 'error'])) {
            // Transaction failed
            $updateData['status'] = 'FAILED';
            $updateData['notes'] = ($this->order->notes ?? '') . 
                "\nDigiflazz transaction failed: " . ($data['message'] ?? 'Unknown error');
            
            Log::error('Order failed via status check', [
                'order_id' => $this->order->id,
                'ref_id' => $this->refId,
                'error' => $data['message'] ?? 'Unknown error',
            ]);

        } elseif (in_array($status, ['pending', 'process'])) {
            // Still processing, schedule another check
            if ($this->attempts() < $this->tries) {
                Log::info('Transaction still pending, will retry', [
                    'order_id' => $this->order->id,
                    'ref_id' => $this->refId,
                    'attempt' => $this->attempts(),
                ]);
                
                // Update metadata but don't change order status
                $this->order->update(['metadata' => $metadata]);
                
                throw new \Exception('Transaction still pending');
            } else {
                // Max attempts reached, consider as failed
                $updateData['status'] = 'FAILED';
                $updateData['notes'] = ($this->order->notes ?? '') . 
                    "\nTransaction timeout after multiple status checks";
                
                Log::error('Transaction timeout after max status checks', [
                    'order_id' => $this->order->id,
                    'ref_id' => $this->refId,
                ]);
            }
        } else {
            // Unknown status
            Log::warning('Unknown transaction status received', [
                'order_id' => $this->order->id,
                'ref_id' => $this->refId,
                'status' => $status,
                'data' => $data,
            ]);
            
            if ($this->attempts() < $this->tries) {
                $this->order->update(['metadata' => $metadata]);
                throw new \Exception('Unknown transaction status: ' . $status);
            }
        }

        // Apply updates
        $updateData['metadata'] = $metadata;
        $this->order->update($updateData);
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(6); // Check status for up to 6 hours
    }
}