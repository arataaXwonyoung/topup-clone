<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\ValidatePlayerIdJob;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ValidationController extends Controller
{
    public function validatePlayerId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|exists:games,id',
            'player_id' => 'required|string|max:50',
            'server_id' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $game = Game::findOrFail($request->game_id);
            
            // Generate a unique session ID for this validation request
            $sessionId = Str::uuid()->toString();
            
            // Check if we need server ID for this game
            if ($game->requires_server && !$request->server_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Server ID is required for this game'
                ], 422);
            }
            
            // Dispatch the validation job
            ValidatePlayerIdJob::dispatch(
                $game->digiflazz_code ?? $game->slug,
                $request->player_id,
                $request->server_id,
                $sessionId
            );
            
            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'message' => 'Validation started. Please check status using the session ID.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to start validation'
            ], 500);
        }
    }
    
    public function checkValidationStatus(Request $request, $sessionId)
    {
        $cacheKey = "player_validation_{$sessionId}";
        $result = Cache::get($cacheKey);
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'status' => 'pending',
                'message' => 'Validation is still in progress or session not found'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'status' => 'completed',
            'data' => $result
        ]);
    }
}
