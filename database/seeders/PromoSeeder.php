<?php

namespace Database\Seeders;

use App\Models\Promo;
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
                'max_discount' => 25000,
                'quota' => 1000,
                'per_user_limit' => 1,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'is_active' => true,
            ],
            [
                'code' => 'WEEKEND15',
                'type' => 'percent',
                'value' => 15,
                'min_total' => 100000,
                'max_discount' => 50000,
                'quota' => 500,
                'per_user_limit' => 2,
                'starts_at' => now(),
                'ends_at' => now()->addWeek(),
                'is_active' => true,
            ],
            [
                'code' => 'CASHBACK5K',
                'type' => 'fixed',
                'value' => 5000,
                'min_total' => 75000,
                'quota' => null,
                'per_user_limit' => 3,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(3),
                'is_active' => true,
            ],
        ];

        foreach ($promos as $promo) {
            Promo::create($promo);
        }
    }
}