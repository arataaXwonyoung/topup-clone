<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index()
    {
        $games = Game::active()
            ->with('denominations')
            ->get();
            
        return view('pages.calculator', compact('games'));
    }
}