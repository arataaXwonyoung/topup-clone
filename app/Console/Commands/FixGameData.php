<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\Denomination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FixGameData extends Command
{
    protected $signature = 'fix:game-data';
    protected $description = 'Fix game and denomination data';

    public function handle()
    {
        $this->info('Fixing game data...');

        // Check games
        $gameCount = Game::count();
        $this->info("Found {$gameCount} games in database");

        if ($gameCount == 0) {
            $this->warn('No games found. Creating sample games...');
            $this->call('db:seed', ['--class' => 'GameSeeder']);
        }

        // Check denominations
        $games = Game::all();
        foreach ($games as $game) {
            $denomCount = Denomination::where('game_id', $game->id)->count();
            
            if ($denomCount == 0) {
                $this->warn("No denominations for {$game->name}. Creating...");
                
                // Create sample denomination
                Denomination::create([
                    'game_id' => $game->id,
                    'name' => 'Sample Pack - 100 Diamonds',
                    'amount' => 100,
                    'bonus' => 10,
                    'price' => 15000,
                    'is_active' => true,
                    'is_hot' => true,
                    'sort_order' => 1,
                    'sku' => strtoupper(substr($game->slug, 0, 3)) . '-100'
                ]);
                
                Denomination::create([
                    'game_id' => $game->id,
                    'name' => 'Premium Pack - 500 Diamonds',
                    'amount' => 500,
                    'bonus' => 50,
                    'price' => 70000,
                    'is_active' => true,
                    'sort_order' => 2,
                    'sku' => strtoupper(substr($game->slug, 0, 3)) . '-500'
                ]);
                
                $this->info("Created 2 sample denominations for {$game->name}");
            } else {
                $this->info("{$game->name} has {$denomCount} denominations");
            }
        }

        // Check admin user
        $admin = User::where('email', 'admin@takapedia.com')->first();
        if (!$admin) {
            $this->warn('Admin user not found. Creating...');
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@takapedia.com',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $this->info('Admin created: admin@takapedia.com / admin123');
        } else {
            $this->info('Admin exists: admin@takapedia.com');
        }

        $this->info('Data fix completed!');
    }
}