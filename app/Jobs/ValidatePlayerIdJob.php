<?php

namespace App\Jobs;

use App\Services\DigiflazzService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ValidatePlayerIdJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    private string $gameCode;
    private string $playerId;
    private ?string $serverId;
    private string $sessionId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $gameCode, string $playerId, ?string $serverId, string $sessionId)
    {
        $this->gameCode = $gameCode;
        $this->playerId = $playerId;
        $this->serverId = $serverId;
        $this->sessionId = $sessionId;
    }

    /**
     * Execute the job.
     */
    public function handle(DigiflazzService $digiflazzService): void
    {
        try {
            Log::info("Starting Player ID validation", [
                'game_code' => $this->gameCode,
                'player_id' => $this->playerId,
                'server_id' => $this->serverId,
                'session_id' => $this->sessionId
            ]);

            // Perform validation via Digiflazz API
            $result = $digiflazzService->validatePlayerId(
                $this->gameCode,
                $this->playerId,
                $this->serverId
            );

            // Store result in cache with session ID as key
            $cacheKey = "player_validation_{$this->sessionId}";
            Cache::put($cacheKey, $result, 300); // Cache for 5 minutes

            Log::info("Player ID validation completed", [
                'session_id' => $this->sessionId,
                'valid' => $result['valid'],
                'player_name' => $result['player_name'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error("Player ID validation job failed", [
                'session_id' => $this->sessionId,
                'error' => $e->getMessage(),
                'game_code' => $this->gameCode,
                'player_id' => $this->playerId
            ]);

            // Store error result in cache
            $cacheKey = "player_validation_{$this->sessionId}";
            Cache::put($cacheKey, [
                'valid' => false,
                'error' => 'Validation service temporarily unavailable',
                'player_name' => null
            ], 300);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Player ID validation job failed permanently", [
            'session_id' => $this->sessionId,
            'error' => $exception->getMessage(),
            'game_code' => $this->gameCode,
            'player_id' => $this->playerId
        ]);

        // Store final failure result in cache
        $cacheKey = "player_validation_{$this->sessionId}";
        Cache::put($cacheKey, [
            'valid' => false,
            'error' => 'Player ID validation failed. Please try again later.',
            'player_name' => null
        ], 300);
    }
}
