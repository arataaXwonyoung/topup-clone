<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Banner;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $category = $request->get('category', 'games');
            $search = $request->get('search');

            $gamesQuery = Game::active();
            
            if ($category !== 'all') {
                $gamesQuery->byCategory($category);
            }
            
            if ($search) {
                $gamesQuery->where('name', 'like', '%' . $search . '%');
            }
            
            $games = $gamesQuery->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(12);

            $banners = Banner::active()
                ->byPosition('hero')
                ->orderBy('sort_order')
                ->get();

            $popularGames = Game::active()
                ->where('is_hot', true)
                ->limit(8)
                ->get();

            return view('home', compact('games', 'banners', 'popularGames', 'category', 'search'));
            
        } catch (\Exception $e) {
            Log::error('Error in HomeController@index: ' . $e->getMessage());
            
            $games = collect();
            $banners = collect();
            $popularGames = collect();
            $category = 'games';
            $search = null;
            
            return view('home', compact('games', 'banners', 'popularGames', 'category', 'search'))
                ->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    public function leaderboard()
    {
        $topSpenders = Order::select('email', 'whatsapp', 
                \DB::raw('COUNT(*) as order_count'), 
                \DB::raw('SUM(total) as total_spent'))
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->where('created_at', '>=', now()->startOfMonth())
            ->groupBy('email', 'whatsapp')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        return view('pages.leaderboard', compact('topSpenders'));
    }

    public function checkTransaction(Request $request)
    {
        $order = null;

        if ($request->has('invoice')) {
            $order = Order::where('invoice_no', $request->invoice)
                ->orWhere('email', $request->invoice)
                ->with(['game', 'denomination', 'payment'])
                ->first();
        }

        return view('pages.check-transaction', compact('order'));
    }
    
    public function orderHistory()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['game', 'denomination', 'payment'])
            ->latest()
            ->paginate(10);
            
        return view('orders.history', compact('orders'));
    }
}