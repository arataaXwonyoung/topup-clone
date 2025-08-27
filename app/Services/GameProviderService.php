<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GameProviderService
{
    /**
     * Process real game top-up via provider APIs
     */
    public function topUpAccount(Order $order): array
    {
        try {
            $gameSlug = $order->game->slug;
            
            return match($gameSlug) {
                'mobile-legends' => $this->topUpMobileLegends($order),
                'free-fire' => $this->topUpFreeFire($order),
                'pubg-mobile' => $this->topUpPubgMobile($order),
                'genshin-impact' => $this->topUpGenshinImpact($order),
                default => $this->mockTopUp($order) // Fallback for testing
            };

        } catch (\Exception $e) {
            Log::error("Game top-up failed for order {$order->invoice_no}: {$e->getMessage()}");
            
            return [
                'success' => false,
                'message' => 'Top-up failed: ' . $e->getMessage(),
                'error_code' => 'TOPUP_FAILED'
            ];
        }
    }

    /**
     * Mobile Legends top-up integration
     */
    protected function topUpMobileLegends(Order $order): array
    {
        $apiUrl = config('payment.game_providers.mobile_legends.api_url');
        $apiKey = config('payment.game_providers.mobile_legends.api_key');
        $merchantId = config('payment.game_providers.mobile_legends.merchant_id');

        if (!$apiUrl || !$apiKey) {
            return $this->mockTopUp($order);
        }

        $payload = [
            'merchant_id' => $merchantId,
            'product_code' => $order->denomination->external_code, // e.g., 'ML275'
            'target' => $order->account_id . '(' . $order->server_id . ')',
            'ref_id' => $order->invoice_no,
            'timestamp' => now()->timestamp,
        ];

        // Add signature for security
        $payload['signature'] = $this->generateSignature($payload, config('payment.game_providers.mobile_legends.api_secret'));

        $response = Http::timeout(30)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-API-Key' => $apiKey
            ])
            ->post($apiUrl . '/topup', $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            if ($data['status'] === 'success') {
                Log::info("ML top-up successful", [
                    'order_id' => $order->id,
                    'trx_id' => $data['trx_id'],
                    'target' => $payload['target']
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $data['trx_id'],
                    'message' => 'Diamonds berhasil dikirim ke akun Mobile Legends',
                    'delivery_time' => '1-5 menit',
                    'delivery_data' => [
                        'game' => 'Mobile Legends',
                        'target_account' => $payload['target'],
                        'diamonds_sent' => $order->denomination->name,
                        'provider_trx_id' => $data['trx_id']
                    ]
                ];
            }
        }

        throw new \Exception('Provider API returned error: ' . ($response->json()['message'] ?? 'Unknown error'));
    }

    /**
     * Free Fire top-up integration
     */
    protected function topUpFreeFire(Order $order): array
    {
        $apiUrl = config('payment.game_providers.free_fire.api_url');
        $apiKey = config('payment.game_providers.free_fire.api_key');

        if (!$apiUrl || !$apiKey) {
            return $this->mockTopUp($order);
        }

        $payload = [
            'player_id' => $order->account_id,
            'product_id' => $order->denomination->external_code, // e.g., 'FF100'
            'order_id' => $order->invoice_no,
        ];

        $response = Http::timeout(30)
            ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
            ->post($apiUrl . '/purchase', $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            return [
                'success' => true,
                'transaction_id' => $data['transaction_id'],
                'message' => 'Diamonds berhasil dikirim ke akun Free Fire',
                'delivery_time' => '1-10 menit',
                'delivery_data' => [
                    'game' => 'Free Fire',
                    'player_id' => $order->account_id,
                    'diamonds_sent' => $order->denomination->name,
                    'provider_trx_id' => $data['transaction_id']
                ]
            ];
        }

        throw new \Exception('Free Fire API error: ' . $response->body());
    }

    /**
     * PUBG Mobile top-up integration  
     */
    protected function topUpPubgMobile(Order $order): array
    {
        // Similar implementation for PUBG Mobile
        return $this->mockTopUp($order);
    }

    /**
     * Genshin Impact top-up integration
     */
    protected function topUpGenshinImpact(Order $order): array
    {
        // Genshin Impact uses HoYoverse API
        return $this->mockTopUp($order);
    }

    /**
     * Mock top-up for testing/unsupported games
     */
    protected function mockTopUp(Order $order): array
    {
        // Simulate processing delay
        sleep(rand(2, 5));

        // 95% success rate for testing
        $success = rand(1, 100) <= 95;

        if ($success) {
            $mockTrxId = 'MOCK_' . time() . '_' . rand(1000, 9999);
            
            Log::info("Mock top-up successful for order {$order->invoice_no}", [
                'mock_trx_id' => $mockTrxId,
                'game' => $order->game->name,
                'target' => $order->account_id
            ]);

            return [
                'success' => true,
                'transaction_id' => $mockTrxId,
                'message' => $order->denomination->name . ' berhasil dikirim!',
                'delivery_time' => '1-5 menit',
                'delivery_data' => [
                    'game' => $order->game->name,
                    'target_account' => $order->account_id,
                    'item_sent' => $order->denomination->name,
                    'provider_trx_id' => $mockTrxId,
                    'note' => 'This is a mock transaction for testing'
                ]
            ];
        }

        throw new \Exception('Mock failure: Temporary system maintenance');
    }

    /**
     * Generate signature for API security
     */
    protected function generateSignature(array $data, string $secretKey): string
    {
        ksort($data);
        $string = '';
        
        foreach ($data as $key => $value) {
            if ($key !== 'signature') {
                $string .= $key . '=' . $value . '&';
            }
        }
        
        $string = rtrim($string, '&') . $secretKey;
        
        return hash('sha256', $string);
    }

    /**
     * Verify account ID exists (for validation before payment)
     */
    public function verifyAccount(string $gameSlug, string $accountId, ?string $serverId = null): array
    {
        try {
            return match($gameSlug) {
                'mobile-legends' => $this->verifyMobileLegends($accountId, $serverId),
                'free-fire' => $this->verifyFreeFire($accountId),
                'pubg-mobile' => $this->verifyPubgMobile($accountId),
                default => [
                    'valid' => true,
                    'username' => 'Player' . substr($accountId, -4),
                    'note' => 'Account validation not available for this game'
                ]
            };

        } catch (\Exception $e) {
            Log::warning("Account verification failed: {$e->getMessage()}");
            
            return [
                'valid' => false,
                'message' => 'Cannot verify account at this time'
            ];
        }
    }

    /**
     * Verify Mobile Legends account
     */
    protected function verifyMobileLegends(string $accountId, ?string $serverId): array
    {
        // Mock verification for now
        if (strlen($accountId) >= 6 && strlen($serverId) >= 4) {
            return [
                'valid' => true,
                'username' => 'Player' . rand(1000, 9999),
                'server_name' => 'Server ' . $serverId
            ];
        }

        return [
            'valid' => false,
            'message' => 'Invalid User ID or Server ID format'
        ];
    }

    /**
     * Verify Free Fire account
     */
    protected function verifyFreeFire(string $accountId): array
    {
        // Mock verification
        if (strlen($accountId) >= 8) {
            return [
                'valid' => true,
                'username' => 'FF_Player' . rand(100, 999),
            ];
        }

        return [
            'valid' => false,
            'message' => 'Invalid Player ID format'
        ];
    }
}