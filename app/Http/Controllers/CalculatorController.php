<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index()
    {
        $games = Game::active()
            ->with(['denominations' => function($query) {
                $query->where('is_active', true)->orderBy('price', 'asc');
            }])
            ->orderBy('name')
            ->get();
            
        // Log for debugging
        \Log::info('Calculator loaded with ' . $games->count() . ' games');
        foreach ($games as $game) {
            \Log::info('Game: ' . $game->name . ' has ' . $game->denominations->count() . ' denominations');
        }
            
        return view('pages.calculator', compact('games'));
    }
}