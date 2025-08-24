<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\Denomination;
use Illuminate\Support\Str;

class FixDataSeeder extends Seeder
{
    public function run(): void
    {
        // Check if games exist
        $gameCount = Game::count();
        echo "Current games in database: {$gameCount}\n";
        
        if ($gameCount == 0) {
            echo "Creating games...\n";
            $this->createGames();
        } else {
            echo "Checking and fixing existing games...\n";
            $this->fixExistingGames();
        }
        
        // Check denominations
        $this->checkAndCreateDenominations();
        
        // Final report
        $activeGames = Game::where('is_active', true)->count();
        $totalDenominations = Denomination::where('is_active', true)->count();
        
        echo "\n=== Final Report ===\n";
        echo "Total active games: {$activeGames}\n";
        echo "Total active denominations: {$totalDenominations}\n";
        echo "===================\n";
    }
    
    private function createGames()
    {
        $games = [
            [
                'name' => 'Mobile Legends',
                'slug' => 'mobile-legends',
                'publisher' => 'Moonton',
                'category' => 'games',
                'is_hot' => true,
                'is_active' => true,
                'requires_server' => true,
                'id_label' => 'User ID',
                'server_label' => 'Server ID',
                'description' => 'Top up Mobile Legends diamonds instant dan murah!',
                'sort_order' => 1
            ],
            [
                'name' => 'Free Fire',
                'slug' => 'free-fire',
                'publisher' => 'Garena',
                'category' => 'games',
                'is_hot' => true,
                'is_active' => true,
                'requires_server' => false,
                'id_label' => 'Player ID',
                'description' => 'Top up Free Fire diamonds dengan harga terbaik!',
                'sort_order' => 2
            ],
            [
                'name' => 'PUBG Mobile',
                'slug' => 'pubg-mobile',
                'publisher' => 'Tencent',
                'category' => 'games',
                'is_hot' => true,
                'is_active' => true,
                'requires_server' => false,
                'id_label' => 'User ID',
                'description' => 'Beli UC PUBG Mobile murah dan instant!',
                'sort_order' => 3
            ],
            [
                'name' => 'Genshin Impact',
                'slug' => 'genshin-impact',
                'publisher' => 'HoYoverse',
                'category' => 'games',
                'is_hot' => true,
                'is_active' => true,
                'requires_server' => true,
                'id_label' => 'UID',
                'server_label' => 'Server',
                'description' => 'Top up Genesis Crystal Genshin Impact!',
                'sort_order' => 4
            ],
            [
                'name' => 'Valorant',
                'slug' => 'valorant',
                'publisher' => 'Riot Games',
                'category' => 'games',
                'is_hot' => false,
                'is_active' => true,
                'requires_server' => false,
                'id_label' => 'Riot ID',
                'description' => 'Beli Valorant Points instant!',
                'sort_order' => 5
            ],
            [
                'name' => 'Call of Duty Mobile',
                'slug' => 'cod-mobile',
                'publisher' => 'Activision',
                'category' => 'games',
                'is_hot' => false,
                'is_active' => true,
                'requires_server' => false,
                'id_label' => 'Player ID',
                'description' => 'Top up CP Call of Duty Mobile!',
                'sort_order' => 6
            ]
        ];
        
        foreach ($games as $gameData) {
            $game = Game::create($gameData);
            echo "Created game: {$game->name}\n";
        }
    }
    
    private function fixExistingGames()
    {
        $games = Game::all();
        
        foreach ($games as $game) {
            $updated = false;
            
            // Fix slug if empty
            if (empty($game->slug)) {
                $game->slug = Str::slug($game->name);
                $updated = true;
            }
            
            // Fix category if empty
            if (empty($game->category)) {
                $game->category = 'games';
                $updated = true;
            }
            
            // Ensure is_active is set
            if (is_null($game->is_active)) {
                $game->is_active = true;
                $updated = true;
            }
            
            // Add description if empty
            if (empty($game->description)) {
                $game->description = "Top up {$game->name} instant dan murah!";
                $updated = true;
            }
            
            if ($updated) {
                $game->save();
                echo "Fixed game: {$game->name}\n";
            }
        }
    }
    
    private function checkAndCreateDenominations()
    {
        $games = Game::where('is_active', true)->get();
        
        foreach ($games as $game) {
            $denomCount = $game->denominations()->where('is_active', true)->count();
            
            if ($denomCount == 0) {
                echo "Creating denominations for {$game->name}...\n";
                $this->createBasicDenominations($game);
            } else {
                echo "{$game->name} has {$denomCount} active denominations\n";
            }
        }
    }
    
    private function createBasicDenominations($game)
    {
        $baseDenominations = [
            ['name' => 'Paket Hemat', 'amount' => 50, 'price' => 15000, 'is_hot' => false],
            ['name' => 'Paket Regular', 'amount' => 100, 'price' => 29000, 'is_hot' => true],
            ['name' => 'Paket Popular', 'amount' => 250, 'price' => 72000, 'is_hot' => true],
            ['name' => 'Paket Elite', 'amount' => 500, 'price' => 143000, 'is_hot' => false],
            ['name' => 'Paket Sultan', 'amount' => 1000, 'price' => 285000, 'is_hot' => false],
        ];
        
        foreach ($baseDenominations as $index => $denomData) {
            Denomination::create([
                'game_id' => $game->id,
                'name' => $denomData['name'],
                'amount' => $denomData['amount'],
                'bonus' => 0,
                'price' => $denomData['price'],
                'is_hot' => $denomData['is_hot'],
                'is_promo' => false,
                'is_active' => true,
                'sort_order' => $index + 1,
                'sku' => strtoupper(Str::slug($game->slug)) . '-' . $denomData['amount']
            ]);
        }
        
        echo "Created " . count($baseDenominations) . " denominations for {$game->name}\n";
    }
}