<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            $category = $request->get('category');
            $sort = $request->get('sort', 'popular'); // popular, name, latest
            
            $gamesQuery = Game::where('is_active', true)
                ->with(['denominations' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->withCount(['orders' => function ($query) {
                    $query->whereIn('status', ['PAID', 'DELIVERED']);
                }]);
            
            // Search functionality
            if ($search) {
                $gamesQuery->where(function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%")
                          ->orWhere('publisher', 'like', "%{$search}%");
                });
            }
            
            // Category filter
            if ($category && $category !== 'all') {
                $gamesQuery->where('category', $category);
            }
            
            // Sorting
            switch ($sort) {
                case 'name':
                    $gamesQuery->orderBy('name');
                    break;
                case 'latest':
                    $gamesQuery->latest();
                    break;
                case 'popular':
                default:
                    $gamesQuery->orderByDesc('orders_count')
                              ->orderBy('sort_order')
                              ->orderBy('name');
                    break;
            }
            
            $games = $gamesQuery->paginate(12)->withQueryString();
            
            // Get available categories
            $categories = Game::where('is_active', true)
                ->select('category')
                ->distinct()
                ->whereNotNull('category')
                ->pluck('category')
                ->filter()
                ->sort()
                ->values();
            
            return view('games.index', compact('games', 'categories', 'search', 'category', 'sort'));
            
        } catch (\Exception $e) {
            Log::error("Error in GameController@index: " . $e->getMessage());
            return view('games.index', [
                'games' => collect()->paginate(12),
                'categories' => collect(),
                'search' => null,
                'category' => null,
                'sort' => 'popular'
            ]);
        }
    }

    public function show($slug)
    {
        try {
            $game = Game::where('slug', $slug)
                ->where('is_active', true)
                ->with(['denominations' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('price');
                }])
                ->firstOrFail();
            
            // Debug untuk memastikan data ada
            Log::info("Game denominations count: " . $game->denominations->count());
            
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