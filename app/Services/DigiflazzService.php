<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class DigiflazzService
{
    protected string $baseUrl;
    protected string $username;
    protected string $apikey;
    protected string $webhookUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.digiflazz.base_url', 'https://api.digiflazz.com/v1');
        $this->username = config('services.digiflazz.username');
        $this->apikey = config('services.digiflazz.api_key');
        $this->webhookUrl = config('services.digiflazz.webhook_url');
    }

    /**
     * Check balance from Digiflazz
     */
    public function cekSaldo(): array
    {
        try {
            $cmd = 'deposit';
            $data = [
                'cmd' => $cmd,
                'username' => $this->username,
                'sign' => $this->generateSignature($cmd)
            ];

            Log::info('Digiflazz balance check request', ['data' => $data]);

            $response = Http::timeout(30)
                ->post($this->baseUrl . '/cek-saldo', $data);

            if (!$response->successful()) {
                Log::error('Digiflazz balance check failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to connect to Digiflazz',
                    'error' => $response->body()
                ];
            }

            $result = $response->json();

            Log::info('Digiflazz balance check response', ['result' => $result]);

            if (isset($result['data']['deposit'])) {
                // Cache balance for 5 minutes
                Cache::put('digiflazz.balance', $result['data']['deposit'], now()->addMinutes(5));

                return [
                    'success' => true,
                    'balance' => $result['data']['deposit'],
                    'data' => $result['data']
                ];
            }

            return [
                'success' => false,
                'message' => $result['data']['message'] ?? 'Unknown error',
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Digiflazz balance check exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Service temporarily unavailable',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get price list from Digiflazz
     */
    public function priceList(bool $forceRefresh = false): array
    {
        try {
            $cacheKey = 'digiflazz.pricelist';
            
            if (!$forceRefresh && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $cmd = 'prepaid';
            $data = [
                'cmd' => $cmd,
                'username' => $this->username,
                'sign' => $this->generateSignature($cmd)
            ];

            Log::info('Digiflazz price list request', ['data' => $data]);

            $response = Http::timeout(60)
                ->post($this->baseUrl . '/price-list', $data);

            if (!$response->successful()) {
                Log::error('Digiflazz price list failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to fetch price list',
                    'error' => $response->body()
                ];
            }

            $result = $response->json();

            if (isset($result['data']) && is_array($result['data'])) {
                // Cache price list for 1 hour
                $priceList = [
                    'success' => true,
                    'data' => $result['data'],
                    'total_products' => count($result['data']),
                    'last_updated' => now()->toISOString()
                ];

                Cache::put($cacheKey, $priceList, now()->addHour());

                Log::info('Digiflazz price list updated', [
                    'total_products' => count($result['data'])
                ]);

                return $priceList;
            }

            return [
                'success' => false,
                'message' => 'Invalid price list response',
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Digiflazz price list exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Service temporarily unavailable',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process top-up transaction
     */
    public function topup(string $refId, string $sku, string $customerId, array $additionalData = []): array
    {
        try {
            $cmd = 'prepaid';
            $data = [
                'cmd' => $cmd,
                'username' => $this->username,
                'buyer_sku_code' => $sku,
                'customer_no' => $customerId,
                'ref_id' => $refId,
                'sign' => $this->generateSignature($cmd, $refId)
            ];

            // Add optional server ID for games that require it
            if (!empty($additionalData['server_id'])) {
                $data['customer_no'] = $customerId . '|' . $additionalData['server_id'];
            }

            // Add testing parameter if in testing mode
            if (config('services.digiflazz.testing', false)) {
                $data['testing'] = true;
            }

            Log::info('Digiflazz topup request', [
                'ref_id' => $refId,
                'sku' => $sku,
                'customer_no' => $customerId,
                'data' => Arr::except($data, ['sign']) // Don't log signature
            ]);

            $response = Http::timeout(60)
                ->post($this->baseUrl . '/transaction', $data);

            if (!$response->successful()) {
                Log::error('Digiflazz topup request failed', [
                    'ref_id' => $refId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to process transaction',
                    'error' => $response->body(),
                    'ref_id' => $refId
                ];
            }

            $result = $response->json();

            Log::info('Digiflazz topup response', [
                'ref_id' => $refId,
                'result' => $result
            ]);

            // Parse response
            if (isset($result['data'])) {
                $responseData = $result['data'];
                
                return [
                    'success' => true,
                    'ref_id' => $refId,
                    'trx_id' => $responseData['trx_id'] ?? null,
                    'status' => $responseData['status'] ?? 'Pending',
                    'message' => $responseData['message'] ?? 'Transaction submitted',
                    'sn' => $responseData['sn'] ?? null,
                    'price' => $responseData['price'] ?? null,
                    'data' => $responseData
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid transaction response',
                'ref_id' => $refId,
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Digiflazz topup exception', [
                'ref_id' => $refId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Service temporarily unavailable',
                'error' => $e->getMessage(),
                'ref_id' => $refId
            ];
        }
    }

    /**
     * Validate Player ID using Digiflazz inquiry
     */
    public function validatePlayerId(string $sku, string $playerId, ?string $serverId = null): array
    {
        try {
            $cacheKey = "digiflazz.validation.{$sku}.{$playerId}" . ($serverId ? ".{$serverId}" : '');
            
            // Check cache first (valid for 10 minutes)
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $cmd = 'inquiry';
            $customerId = $serverId ? "{$playerId}|{$serverId}" : $playerId;
            
            $data = [
                'cmd' => $cmd,
                'username' => $this->username,
                'buyer_sku_code' => $sku,
                'customer_no' => $customerId,
                'sign' => $this->generateSignature($cmd)
            ];

            Log::info('Digiflazz player validation request', [
                'sku' => $sku,
                'customer_no' => $customerId,
                'data' => Arr::except($data, ['sign'])
            ]);

            $response = Http::timeout(30)
                ->post($this->baseUrl . '/inquiry', $data);

            if (!$response->successful()) {
                Log::error('Digiflazz validation failed', [
                    'sku' => $sku,
                    'customer_no' => $customerId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                $result = [
                    'success' => false,
                    'message' => 'Validation service unavailable',
                    'error' => $response->body()
                ];

                // Cache failed result for 2 minutes
                Cache::put($cacheKey, $result, now()->addMinutes(2));
                return $result;
            }

            $result = $response->json();

            Log::info('Digiflazz validation response', [
                'sku' => $sku,
                'customer_no' => $customerId,
                'result' => $result
            ]);

            if (isset($result['data'])) {
                $responseData = $result['data'];
                $isValid = isset($responseData['customer_name']) && !empty($responseData['customer_name']);
                
                $validationResult = [
                    'success' => $isValid,
                    'valid' => $isValid,
                    'customer_name' => $responseData['customer_name'] ?? null,
                    'message' => $isValid ? 'Player ID is valid' : 'Player ID not found',
                    'data' => $responseData
                ];

                // Cache result for 10 minutes
                Cache::put($cacheKey, $validationResult, now()->addMinutes(10));
                return $validationResult;
            }

            $result = [
                'success' => false,
                'valid' => false,
                'message' => 'Invalid validation response',
                'data' => $result
            ];

            Cache::put($cacheKey, $result, now()->addMinutes(2));
            return $result;

        } catch (\Exception $e) {
            Log::error('Digiflazz validation exception', [
                'sku' => $sku,
                'customer_no' => $playerId,
                'error' => $e->getMessage()
            ]);

            $result = [
                'success' => false,
                'valid' => false,
                'message' => 'Validation service error',
                'error' => $e->getMessage()
            ];

            Cache::put($cacheKey, $result, now()->addMinutes(2));
            return $result;
        }
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus(string $refId): array
    {
        try {
            $cmd = 'status';
            $data = [
                'cmd' => $cmd,
                'username' => $this->username,
                'ref_id' => $refId,
                'sign' => $this->generateSignature($cmd, $refId)
            ];

            Log::info('Digiflazz status check request', [
                'ref_id' => $refId,
                'data' => Arr::except($data, ['sign'])
            ]);

            $response = Http::timeout(30)
                ->post($this->baseUrl . '/transaction', $data);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to check status',
                    'error' => $response->body()
                ];
            }

            $result = $response->json();

            Log::info('Digiflazz status check response', [
                'ref_id' => $refId,
                'result' => $result
            ]);

            if (isset($result['data'])) {
                return [
                    'success' => true,
                    'data' => $result['data']
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid status response',
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Digiflazz status check exception', [
                'ref_id' => $refId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Service error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate signature for API request
     */
    protected function generateSignature(string $cmd, ?string $refId = null): string
    {
        $string = match ($cmd) {
            'deposit' => $this->username . $this->apikey . 'depo',
            'prepaid' => $this->username . $this->apikey . ($refId ?? 'pricelist'),
            'inquiry' => $this->username . $this->apikey . 'inquiry',
            'status' => $this->username . $this->apikey . ($refId ?? ''),
            default => $this->username . $this->apikey
        };

        return md5($string);
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(array $data, string $signature): bool
    {
        try {
            // Digiflazz webhook signature format
            $string = $this->username . $this->apikey . ($data['ref_id'] ?? '');
            $expectedSignature = md5($string);

            return hash_equals($expectedSignature, $signature);

        } catch (\Exception $e) {
            Log::error('Digiflazz webhook signature verification failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return false;
        }
    }

    /**
     * Get cached balance
     */
    public function getCachedBalance(): ?float
    {
        return Cache::get('digiflazz.balance');
    }

    /**
     * Get product by SKU from cached price list
     */
    public function getProductBySku(string $sku): ?array
    {
        $priceList = $this->priceList();
        
        if (!$priceList['success']) {
            return null;
        }

        foreach ($priceList['data'] as $product) {
            if ($product['buyer_sku_code'] === $sku) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(string $category): array
    {
        $priceList = $this->priceList();
        
        if (!$priceList['success']) {
            return [];
        }

        return array_filter($priceList['data'], function ($product) use ($category) {
            return stripos($product['category'], $category) !== false;
        });
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        try {
            $result = $this->cekSaldo();
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'balance' => $result['balance'] ?? null
                ];
            }

            return [
                'success' => false,
                'message' => 'Connection failed: ' . ($result['message'] ?? 'Unknown error')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Legacy methods for backward compatibility
     */
    public function getBalance(): array
    {
        $result = $this->cekSaldo();
        return [
            'balance' => $result['success'] ? $result['balance'] : 0,
            'error' => $result['success'] ? null : $result['message']
        ];
    }

    public function getGameList(): array
    {
        $result = $this->priceList();
        return $result['success'] ? $result['data'] : [];
    }
}