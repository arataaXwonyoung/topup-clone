<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    public function show($slug)
    {
        try {
            // Debug log
            Log::info("Accessing game: {$slug}");
            
            $game = Game::where('slug', $slug)
                ->where('is_active', true)
                ->with(['denominations' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('price');
                }])
                ->firstOrFail();
            
            // Debug: Check denominations
            Log::info("Game found: {$game->name}");
            Log::info("Denominations count: " . $game->denominations->count());
            
            $reviews = Review::where('game_id', $game->id)
                ->where('is_approved', true)
                ->with('user')
                ->latest()
                ->limit(10)
                ->get();

            $relatedGames = Game::active()
                ->where('category', $game->category)
                ->where('id', '!=', $game->id)
                ->limit(4)
                ->get();

            return view('games.show', compact('game', 'reviews', 'relatedGames'));
            
        } catch (\Exception $e) {
            Log::error("Error in GameController@show: " . $e->getMessage());
            abort(404, 'Game not found');
        }
    }
}