<?php

namespace App\Http\Controllers;

use App\Models\WebhookLog;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected PaymentManager $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    public function handle(Request $request, $provider)
    {
        // Log webhook
        $log = WebhookLog::create([
            'provider' => $provider,
            'headers' => $request->headers->all(),
            'raw_payload' => $request->all(),
            'status' => 'PENDING',
        ]);

        try {
            $driver = $this->paymentManager->driver($provider);
            
            // Verify signature
            $signature = $request->header('X-Callback-Signature') 
                ?? $request->header('X-Signature') 
                ?? '';
            
            if (!$driver->verifyWebhookSignature($signature, $request->all())) {
                throw new \Exception('Invalid webhook signature');
            }

            // Handle webhook
            $result = $driver->handleWebhook($request->all());
            
            $log->update([
                'status' => $result ? 'PROCESSED' : 'FAILED',
                'processed_at' => now(),
            ]);

            return response()->json(['status' => 'OK']);
        } catch (\Exception $e) {
            $log->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}