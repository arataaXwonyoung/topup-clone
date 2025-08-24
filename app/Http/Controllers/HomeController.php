<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Article;
use App\Models\Banner;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        try {
            // Get active games with denominations
            $games = Game::where('is_active', true)
                ->with(['denominations' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
            
            // Log for debugging
            Log::info('Games loaded: ' . $games->count());
            
            // Get featured articles
            $articles = Article::where('is_published', true)
                ->where('is_featured', true)
                ->latest('published_at')
                ->limit(3)
                ->get();
            
            // Get active banners
            $banners = Banner::where('is_active', true)
                ->where('position', 'hero')
                ->orderBy('sort_order')
                ->get();
            
            // Get recent reviews
            $reviews = Review::with(['user', 'game'])
                ->where('is_approved', true)
                ->latest()
                ->limit(5)
                ->get();
            
            return view('home', compact('games', 'articles', 'banners', 'reviews'));
            
        } catch (\Exception $e) {
            Log::error('Error in HomeController@index: ' . $e->getMessage());
            
            // Return view with empty collections to prevent errors
            return view('home', [
                'games' => collect(),
                'articles' => collect(),
                'banners' => collect(),
                'reviews' => collect()
            ]);
        }
    }
    
    public function leaderboard()
    {
        // Get top spenders
        $topSpenders = \App\Models\User::select('users.*')
            ->selectRaw('COALESCE(SUM(orders.total), 0) as total_spent')
            ->leftJoin('orders', function($join) {
                $join->on('users.id', '=', 'orders.user_id')
                    ->whereIn('orders.status', ['PAID', 'DELIVERED']);
            })
            ->groupBy('users.id')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();
        
        return view('leaderboard', compact('topSpenders'));
    }
    
    public function checkTransaction(Request $request)
    {
        $order = null;
        
        if ($request->has('invoice')) {
            $order = \App\Models\Order::where('invoice_no', $request->invoice)
                ->with(['game', 'denomination', 'payment'])
                ->first();
        }
        
        return view('check-transaction', compact('order'));
    }
    
    public function orderHistory()
    {
        $orders = auth()->user()->orders()
            ->with(['game', 'denomination', 'payment'])
            ->latest()
            ->paginate(10);
            
        return view('orders.history', compact('orders'));
    }
    
}