<?php

namespace Database\Seeders;

use App\Models\Denomination;
use App\Models\Game;
use Illuminate\Database\Seeder;

class DenominationSeeder extends Seeder
{
    public function run(): void
    {
        $denominations = [
            'Mobile Legends' => [
                ['name' => '86 Diamonds', 'amount' => 86, 'bonus' => 0, 'price' => 20000, 'original_price' => 22000, 'is_hot' => true],
                ['name' => '172 Diamonds', 'amount' => 172, 'bonus' => 0, 'price' => 39000, 'original_price' => 42000],
                ['name' => '257 Diamonds', 'amount' => 257, 'bonus' => 0, 'price' => 58000, 'original_price' => 62000],
                ['name' => '344 Diamonds', 'amount' => 344, 'bonus' => 0, 'price' => 77000, 'original_price' => 82000],
                ['name' => '514 Diamonds', 'amount' => 514, 'bonus' => 0, 'price' => 115000, 'original_price' => 122000],
                ['name' => '706 Diamonds', 'amount' => 706, 'bonus' => 0, 'price' => 154000, 'original_price' => 162000],
                ['name' => '878 Diamonds', 'amount' => 878, 'bonus' => 0, 'price' => 192000, 'original_price' => 202000],
                ['name' => '2195 Diamonds', 'amount' => 2195, 'bonus' => 0, 'price' => 462000, 'original_price' => 482000],
                ['name' => 'Weekly Diamond Pass', 'amount' => 1, 'bonus' => 0, 'price' => 28000, 'original_price' => 30000, 'is_promo' => true],
                ['name' => 'Twilight Pass', 'amount' => 1, 'bonus' => 0, 'price' => 145000, 'original_price' => 150000],
            ],
            'Free Fire' => [
                ['name' => '50 Diamonds', 'amount' => 50, 'bonus' => 0, 'price' => 7500, 'original_price' => 8000, 'is_hot' => true],
                ['name' => '70 Diamonds', 'amount' => 70, 'bonus' => 0, 'price' => 10000, 'original_price' => 11000],
                ['name' => '140 Diamonds', 'amount' => 140, 'bonus' => 0, 'price' => 19500, 'original_price' => 21000],
                ['name' => '210 Diamonds', 'amount' => 210, 'bonus' => 0, 'price' => 29000, 'original_price' => 31000],
                ['name' => '355 Diamonds', 'amount' => 355, 'bonus' => 0, 'price' => 48000, 'original_price' => 51000],
                ['name' => '720 Diamonds', 'amount' => 720, 'bonus' => 0, 'price' => 95000, 'original_price' => 100000],
                ['name' => '1450 Diamonds', 'amount' => 1450, 'bonus' => 0, 'price' => 190000, 'original_price' => 200000],
                ['name' => 'Member Mingguan', 'amount' => 1, 'bonus' => 0, 'price' => 29000, 'original_price' => 31000, 'is_promo' => true],
                ['name' => 'Member Bulanan', 'amount' => 1, 'bonus' => 0, 'price' => 85000, 'original_price' => 90000],
            ],
            'PUBG Mobile' => [
                ['name' => '60 UC', 'amount' => 60, 'bonus' => 0, 'price' => 15000, 'original_price' => 16000, 'is_hot' => true],
                ['name' => '325 UC', 'amount' => 325, 'bonus' => 0, 'price' => 75000, 'original_price' => 79000],
                ['name' => '660 UC', 'amount' => 660, 'bonus' => 0, 'price' => 150000, 'original_price' => 156000],
                ['name' => '1800 UC', 'amount' => 1800, 'bonus' => 0, 'price' => 375000, 'original_price' => 390000],
                ['name' => '3850 UC', 'amount' => 3850, 'bonus' => 0, 'price' => 750000, 'original_price' => 780000],
                ['name' => '8100 UC', 'amount' => 8100, 'bonus' => 0, 'price' => 1500000, 'original_price' => 1560000],
                ['name' => 'Royale Pass', 'amount' => 1, 'bonus' => 0, 'price' => 135000, 'original_price' => 140000, 'is_promo' => true],
            ],
            'Genshin Impact' => [
                ['name' => '60 Genesis Crystals', 'amount' => 60, 'bonus' => 0, 'price' => 16000, 'original_price' => 17000],
                ['name' => '300+30 Genesis Crystals', 'amount' => 300, 'bonus' => 30, 'price' => 79000, 'original_price' => 85000, 'is_hot' => true],
                ['name' => '980+110 Genesis Crystals', 'amount' => 980, 'bonus' => 110, 'price' => 249000, 'original_price' => 260000],
                ['name' => '1980+260 Genesis Crystals', 'amount' => 1980, 'bonus' => 260, 'price' => 479000, 'original_price' => 500000],
                ['name' => '3280+600 Genesis Crystals', 'amount' => 3280, 'bonus' => 600, 'price' => 799000, 'original_price' => 830000],
                ['name' => '6480+1600 Genesis Crystals', 'amount' => 6480, 'bonus' => 1600, 'price' => 1599000, 'original_price' => 1650000],
                ['name' => 'Blessing of the Welkin Moon', 'amount' => 1, 'bonus' => 0, 'price' => 79000, 'original_price' => 85000, 'is_promo' => true],
            ],
            'Valorant' => [
                ['name' => '125 Points', 'amount' => 125, 'bonus' => 0, 'price' => 15000, 'original_price' => 16000],
                ['name' => '420 Points', 'amount' => 420, 'bonus' => 0, 'price' => 49000, 'original_price' => 52000, 'is_hot' => true],
                ['name' => '700 Points', 'amount' => 700, 'bonus' => 0, 'price' => 80000, 'original_price' => 85000],
                ['name' => '1375 Points', 'amount' => 1375, 'bonus' => 0, 'price' => 150000, 'original_price' => 160000],
                ['name' => '2400 Points', 'amount' => 2400, 'bonus' => 0, 'price' => 250000, 'original_price' => 265000],
                ['name' => '4000 Points', 'amount' => 4000, 'bonus' => 0, 'price' => 400000, 'original_price' => 420000],
                ['name' => '8150 Points', 'amount' => 8150, 'bonus' => 0, 'price' => 800000, 'original_price' => 835000],
            ],
            'Call of Duty Mobile' => [
                ['name' => '53 CP', 'amount' => 53, 'bonus' => 0, 'price' => 10000, 'original_price' => 11000],
                ['name' => '106 CP', 'amount' => 106, 'bonus' => 0, 'price' => 20000, 'original_price' => 21000, 'is_hot' => true],
                ['name' => '264 CP', 'amount' => 264, 'bonus' => 0, 'price' => 49000, 'original_price' => 52000],
                ['name' => '528 CP', 'amount' => 528, 'bonus' => 0, 'price' => 95000, 'original_price' => 100000],
                ['name' => '1056 CP', 'amount' => 1056, 'bonus' => 0, 'price' => 189000, 'original_price' => 200000],
                ['name' => '2118 CP', 'amount' => 2118, 'bonus' => 0, 'price' => 379000, 'original_price' => 400000],
                ['name' => '5430 CP', 'amount' => 5430, 'bonus' => 0, 'price' => 949000, 'original_price' => 1000000],
            ],
            'Roblox' => [
                ['name' => '200 Robux', 'amount' => 200, 'bonus' => 0, 'price' => 39000, 'original_price' => 42000],
                ['name' => '400 Robux', 'amount' => 400, 'bonus' => 0, 'price' => 75000, 'original_price' => 80000, 'is_hot' => true],
                ['name' => '800 Robux', 'amount' => 800, 'bonus' => 0, 'price' => 150000, 'original_price' => 160000],
                ['name' => '1700 Robux', 'amount' => 1700, 'bonus' => 0, 'price' => 299000, 'original_price' => 320000],
                ['name' => '2200 Robux', 'amount' => 2200, 'bonus' => 0, 'price' => 389000, 'original_price' => 410000],
                ['name' => '4500 Robux', 'amount' => 4500, 'bonus' => 0, 'price' => 779000, 'original_price' => 820000],
                ['name' => '10000 Robux', 'amount' => 10000, 'bonus' => 0, 'price' => 1699000, 'original_price' => 1800000],
            ],
        ];

        foreach ($denominations as $gameName => $items) {
            $game = Game::where('name', $gameName)->first();
            
            if ($game) {
                foreach ($items as $index => $item) {
                    Denomination::create([
                        'game_id' => $game->id,
                        'name' => $item['name'],
                        'amount' => $item['amount'],
                        'bonus' => $item['bonus'],
                        'price' => $item['price'],
                        'original_price' => $item['original_price'] ?? $item['price'],
                        'is_hot' => $item['is_hot'] ?? false,
                        'is_promo' => $item['is_promo'] ?? false,
                        'is_active' => true,
                        'sort_order' => $index,
                        'sku' => strtoupper(str_replace(' ', '_', $gameName)) . '_' . ($index + 1),
                    ]);
                }
            }
        }
    }
}