<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\Denomination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckAndFixSeeder extends Seeder
{
    public function run(): void
    {
        // Check if games exist
        $gameCount = Game::count();
        echo "Current games in database: {$gameCount}\n";
        
        if ($gameCount == 0) {
            echo "No games found! Running GameSeeder...\n";
            $this->call(GameSeeder::class);
            $this->call(DenominationSeeder::class);
            echo "Games seeded successfully!\n";
        } else {
            echo "Games already exist. Checking data integrity...\n";
            
            // Check for missing fields
            $games = Game::all();
            foreach ($games as $game) {
                $updated = false;
                
                if (empty($game->slug)) {
                    $game->slug = \Str::slug($game->name);
                    $updated = true;
                }
                
                if (empty($game->category)) {
                    $game->category = 'games';
                    $updated = true;
                }
                
                if (is_null($game->is_active)) {
                    $game->is_active = true;
                    $updated = true;
                }
                
                if ($updated) {
                    $game->save();
                    echo "Fixed game: {$game->name}\n";
                }
            }
        }
        
        // Verify final count
        $finalCount = Game::active()->count();
        echo "Total active games: {$finalCount}\n";
    }
}