<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $provider): Response
    {
        $signature = $this->getSignature($request, $provider);
        $payload = $request->all();

        if (!$this->verifySignature($signature, $payload, $provider)) {
            return response()->json([
                'error' => 'Invalid signature'
            ], 401);
        }

        return $next($request);
    }

    protected function getSignature(Request $request, string $provider): string
    {
        return match($provider) {
            'midtrans' => $request->header('X-Midtrans-Signature', ''),
            'xendit' => $request->header('X-Callback-Token', ''),
            'tripay' => $request->header('X-Callback-Signature', ''),
            default => ''
        };
    }

    protected function verifySignature(string $signature, array $payload, string $provider): bool
    {
        return match($provider) {
            'midtrans' => $this->verifyMidtransSignature($signature, $payload),
            'xendit' => $this->verifyXenditSignature($signature),
            'tripay' => $this->verifyTripaySignature($signature, $payload),
            default => false
        };
    }

    protected function verifyMidtransSignature(string $signature, array $payload): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = config('services.midtrans.server_key');
        
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        return hash_equals($expectedSignature, $signature);
    }

    protected function verifyXenditSignature(string $token): bool
    {
        return hash_equals(config('services.xendit.callback_token'), $token);
    }

    protected function verifyTripaySignature(string $signature, array $payload): bool
    {
        $privateKey = config('services.tripay.private_key');
        $jsonPayload = json_encode($payload);
        
        $expectedSignature = hash_hmac('sha256', $jsonPayload, $privateKey);
        
        return hash_equals($expectedSignature, $signature);
    }
}