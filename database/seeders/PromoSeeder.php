<?php

namespace Database\Seeders;

use App\Models\Promo;
use App\Models\Game;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        $promos = [
            [
                'code' => 'NEWUSER10',
                'type' => 'percent',
                'value' => 10,
                'min_total' => 50000,
                'max_discount' => 50000,
                'quota' => 1000,
                'used_count' => 0,
                'per_user_limit' => 1,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(3),
                'is_active' => true,
                'game_ids' => null, // All games
            ],
            [
                'code' => 'WEEKEND20',
                'type' => 'percent',
                'value' => 20,
                'min_total' => 100000,
                'max_discount' => 100000,
                'quota' => 500,
                'used_count' => 0,
                'per_user_limit' => 2,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'is_active' => true,
                'game_ids' => null,
            ],
            [
                'code' => 'MLBB5K',
                'type' => 'fixed',
                'value' => 5000,
                'min_total' => 50000,
                'max_discount' => null,
                'quota' => 200,
                'used_count' => 0,
                'per_user_limit' => 3,
                'starts_at' => now(),
                'ends_at' => now()->addWeeks(2),
                'is_active' => true,
                'game_ids' => [1], // Mobile Legends only
            ],
            [
                'code' => 'FREEFIRE15',
                'type' => 'percent',
                'value' => 15,
                'min_total' => 30000,
                'max_discount' => 30000,
                'quota' => 300,
                'used_count' => 0,
                'per_user_limit' => 2,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'is_active' => true,
                'game_ids' => [2], // Free Fire only
            ],
            [
                'code' => 'GENSHIN25',
                'type' => 'percent',
                'value' => 25,
                'min_total' => 200000,
                'max_discount' => 200000,
                'quota' => 100,
                'used_count' => 0,
                'per_user_limit' => 1,
                'starts_at' => now(),
                'ends_at' => now()->addWeeks(1),
                'is_active' => true,
                'game_ids' => [4], // Genshin Impact only
            ],
            [
                'code' => 'TOPUP10K',
                'type' => 'fixed',
                'value' => 10000,
                'min_total' => 100000,
                'max_discount' => null,
                'quota' => null, // Unlimited
                'used_count' => 0,
                'per_user_limit' => 5,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(6),
                'is_active' => true,
                'game_ids' => null,
            ],
            [
                'code' => 'LOYAL50K',
                'type' => 'fixed',
                'value' => 50000,
                'min_total' => 500000,
                'max_discount' => null,
                'quota' => 50,
                'used_count' => 0,
                'per_user_limit' => 1,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'is_active' => true,
                'game_ids' => null,
            ],
            [
                'code' => 'FLASH30',
                'type' => 'percent',
                'value' => 30,
                'min_total' => 150000,
                'max_discount' => 150000,
                'quota' => 100,
                'used_count' => 0,
                'per_user_limit' => 1,
                'starts_at' => now()->addDays(7),
                'ends_at' => now()->addDays(8),
                'is_active' => true,
                'game_ids' => null,
            ],
        ];

        foreach ($promos as $promo) {
            Promo::create($promo);
        }
    }
}