<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Auth::user()->wishlists()->with('game')->paginate(12);
        
        return view('user.wishlists.index', compact('wishlists'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id'
        ]);

        $userId = Auth::id();
        $gameId = $request->game_id;

        $existing = Wishlist::where('user_id', $userId)
            ->where('game_id', $gameId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Game sudah ada di wishlist'
            ]);
        }

        Wishlist::create([
            'user_id' => $userId,
            'game_id' => $gameId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Game berhasil ditambahkan ke wishlist'
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id'
        ]);

        $deleted = Wishlist::where('user_id', Auth::id())
            ->where('game_id', $request->game_id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Game berhasil dihapus dari wishlist'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Game tidak ditemukan di wishlist'
        ]);
    }

    public function toggle(Request $request)
    {
        \Log::info('Wishlist toggle request:', $request->all());
        
        $request->validate([
            'game_id' => 'required|exists:games,id'
        ]);

        $userId = Auth::id();
        $gameId = $request->game_id;
        
        \Log::info('Toggle wishlist for user: ' . $userId . ', game: ' . $gameId);

        $existing = Wishlist::where('user_id', $userId)
            ->where('game_id', $gameId)
            ->first();

        if ($existing) {
            $existing->delete();
            \Log::info('Game removed from wishlist');
            return response()->json([
                'success' => true,
                'action' => 'removed',
                'message' => 'Game dihapus dari wishlist'
            ]);
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'game_id' => $gameId
            ]);
            \Log::info('Game added to wishlist');
            
            return response()->json([
                'success' => true,
                'action' => 'added',
                'message' => 'Game ditambahkan ke wishlist'
            ]);
        }
    }
}