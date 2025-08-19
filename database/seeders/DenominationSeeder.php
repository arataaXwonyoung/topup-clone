<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Denomination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DenominationSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing denominations
        DB::table('denominations')->truncate();
        
        // Mobile Legends
        $mlbb = Game::where('slug', 'mobile-legends')->first();
        if ($mlbb) {
            $this->createMLBBDenominations($mlbb->id);
            echo "Created denominations for Mobile Legends\n";
        }

        // Free Fire
        $ff = Game::where('slug', 'free-fire')->first();
        if ($ff) {
            $this->createFFDenominations($ff->id);
            echo "Created denominations for Free Fire\n";
        }

        // PUBG Mobile
        $pubg = Game::where('slug', 'pubg-mobile')->first();
        if ($pubg) {
            $this->createPUBGDenominations($pubg->id);
            echo "Created denominations for PUBG Mobile\n";
        }

        // Genshin Impact
        $genshin = Game::where('slug', 'genshin-impact')->first();
        if ($genshin) {
            $this->createGenshinDenominations($genshin->id);
            echo "Created denominations for Genshin Impact\n";
        }
        
        // Valorant
        $valorant = Game::where('slug', 'valorant')->first();
        if ($valorant) {
            $this->createValorantDenominations($valorant->id);
            echo "Created denominations for Valorant\n";
        }
        
        // Call of Duty Mobile
        $cod = Game::where('slug', 'cod-mobile')->first();
        if ($cod) {
            $this->createCODDenominations($cod->id);
            echo "Created denominations for Call of Duty Mobile\n";
        }
        
        // Add more games...
        echo "All denominations seeded successfully!\n";
    }

    protected function createMLBBDenominations($gameId): void
    {
        $denominations = [
            // Special Passes
            ['name' => 'Weekly Diamond Pass', 'amount' => 0, 'bonus' => 0, 'price' => 26777, 'is_hot' => true, 'sort_order' => 1],
            ['name' => '2x Weekly Diamond Pass', 'amount' => 0, 'bonus' => 0, 'price' => 53554, 'sort_order' => 2],
            ['name' => '3x Weekly Diamond Pass', 'amount' => 0, 'bonus' => 0, 'price' => 80331, 'sort_order' => 3],
            ['name' => 'Twilight Pass', 'amount' => 0, 'bonus' => 0, 'price' => 147777, 'is_hot' => true, 'sort_order' => 4],
            
            // Regular Diamonds
            ['name' => '86 Diamonds', 'amount' => 78, 'bonus' => 8, 'price' => 19900, 'sort_order' => 5],
            ['name' => '172 Diamonds', 'amount' => 156, 'bonus' => 16, 'price' => 39500, 'sort_order' => 6],
            ['name' => '257 Diamonds', 'amount' => 234, 'bonus' => 23, 'price' => 59200, 'sort_order' => 7],
            ['name' => '344 Diamonds', 'amount' => 312, 'bonus' => 32, 'price' => 78900, 'sort_order' => 8],
            ['name' => '429 Diamonds', 'amount' => 390, 'bonus' => 39, 'price' => 98600, 'sort_order' => 9],
            ['name' => '514 Diamonds', 'amount' => 468, 'bonus' => 46, 'price' => 118300, 'sort_order' => 10],
            ['name' => '706 Diamonds', 'amount' => 625, 'bonus' => 81, 'price' => 157700, 'is_hot' => true, 'sort_order' => 11],
            ['name' => '878 Diamonds', 'amount' => 781, 'bonus' => 97, 'price' => 197100, 'sort_order' => 12],
            ['name' => '1412 Diamonds', 'amount' => 1250, 'bonus' => 162, 'price' => 315500, 'sort_order' => 13],
            ['name' => '2195 Diamonds', 'amount' => 1860, 'bonus' => 335, 'price' => 472900, 'sort_order' => 14],
            ['name' => '3688 Diamonds', 'amount' => 3099, 'bonus' => 589, 'price' => 788500, 'sort_order' => 15],
            ['name' => '5532 Diamonds', 'amount' => 4649, 'bonus' => 883, 'price' => 1182700, 'sort_order' => 16],
        ];

        foreach ($denominations as $denom) {
            Denomination::create([
                'game_id' => $gameId,
                'name' => $denom['name'],
                'amount' => $denom['amount'],
                'bonus' => $denom['bonus'],
                'price' => $denom['price'],
                'is_hot' => $denom['is_hot'] ?? false,
                'is_promo' => false,
                'is_active' => true,
                'sort_order' => $denom['sort_order'],
                'sku' => 'MLBB-' . str_replace(' ', '-', $denom['name']),
            ]);
        }
    }

    protected function createFFDenominations($gameId): void
    {
        $denominations = [
            ['name' => '50 Diamonds', 'amount' => 50, 'price' => 7200, 'sort_order' => 1],
            ['name' => '70 Diamonds', 'amount' => 70, 'price' => 9900, 'sort_order' => 2],
            ['name' => '100 Diamonds', 'amount' => 100, 'price' => 14000, 'is_hot' => true, 'sort_order' => 3],
            ['name' => '140 Diamonds', 'amount' => 140, 'price' => 19800, 'sort_order' => 4],
            ['name' => '210 Diamonds', 'amount' => 210, 'price' => 29700, 'sort_order' => 5],
            ['name' => '280 Diamonds', 'amount' => 280, 'price' => 39600, 'sort_order' => 6],
            ['name' => '355 Diamonds', 'amount' => 355, 'price' => 49500, 'sort_order' => 7],
            ['name' => '500 Diamonds', 'amount' => 500, 'price' => 69300, 'is_hot' => true, 'sort_order' => 8],
            ['name' => '720 Diamonds', 'amount' => 720, 'price' => 99000, 'sort_order' => 9],
            ['name' => '1000 Diamonds', 'amount' => 1000, 'price' => 138600, 'sort_order' => 10],
            ['name' => '1450 Diamonds', 'amount' => 1450, 'price' => 198000, 'sort_order' => 11],
            ['name' => '2180 Diamonds', 'amount' => 2180, 'price' => 297000, 'sort_order' => 12],
            ['name' => 'Member Mingguan', 'amount' => 0, 'price' => 29700, 'sort_order' => 13],
            ['name' => 'Member Bulanan', 'amount' => 0, 'price' => 89100, 'is_hot' => true, 'sort_order' => 14],
        ];

        foreach ($denominations as $denom) {
            Denomination::create([
                'game_id' => $gameId,
                'name' => $denom['name'],
                'amount' => $denom['amount'],
                'bonus' => 0,
                'price' => $denom['price'],
                'is_hot' => $denom['is_hot'] ?? false,
                'is_promo' => false,
                'is_active' => true,
                'sort_order' => $denom['sort_order'],
                'sku' => 'FF-' . str_replace(' ', '-', $denom['name']),
            ]);
        }
    }

    protected function createPUBGDenominations($gameId): void
    {
        $denominations = [
            ['name' => '60 UC', 'amount' => 60, 'price' => 14500, 'sort_order' => 1],
            ['name' => '325 UC', 'amount' => 325, 'price' => 72500, 'is_hot' => true, 'sort_order' => 2],
            ['name' => '660 UC', 'amount' => 660, 'price' => 145000, 'sort_order' => 3],
            ['name' => '1800 UC', 'amount' => 1800, 'price' => 362500, 'sort_order' => 4],
            ['name' => '3850 UC', 'amount' => 3850, 'price' => 725000, 'sort_order' => 5],
            ['name' => '8100 UC', 'amount' => 8100, 'price' => 1450000, 'sort_order' => 6],
        ];

        foreach ($denominations as $denom) {
            Denomination::create([
                'game_id' => $gameId,
                'name' => $denom['name'],
                'amount' => $denom['amount'],
                'bonus' => 0,
                'price' => $denom['price'],
                'is_hot' => $denom['is_hot'] ?? false,
                'is_promo' => false,
                'is_active' => true,
                'sort_order' => $denom['sort_order'],
                'sku' => 'PUBG-' . $denom['amount'],
            ]);
        }
    }

    protected function createGenshinDenominations($gameId): void
    {
        $denominations = [
            ['name' => 'Blessing of the Welkin Moon', 'amount' => 0, 'price' => 65000, 'is_hot' => true, 'sort_order' => 1],
            ['name' => '60 Genesis Crystals', 'amount' => 60, 'price' => 15000, 'sort_order' => 2],
            ['name' => '330 Genesis Crystals', 'amount' => 330, 'price' => 75000, 'sort_order' => 3],
            ['name' => '1090 Genesis Crystals', 'amount' => 1090, 'price' => 225000, 'is_hot' => true, 'sort_order' => 4],
            ['name' => '2240 Genesis Crystals', 'amount' => 2240, 'price' => 450000, 'sort_order' => 5],
            ['name' => '3880 Genesis Crystals', 'amount' => 3880, 'price' => 750000, 'sort_order' => 6],
            ['name' => '8080 Genesis Crystals', 'amount' => 8080, 'price' => 1500000, 'sort_order' => 7],
        ];

        foreach ($denominations as $denom) {
            Denomination::create([
                'game_id' => $gameId,
                'name' => $denom['name'],
                'amount' => $denom['amount'],
                'bonus' => 0,
                'price' => $denom['price'],
                'is_hot' => $denom['is_hot'] ?? false,
                'is_promo' => false,
                'is_active' => true,
                'sort_order' => $denom['sort_order'],
                'sku' => 'GI-' . ($denom['amount'] ?: 'WELKIN'),
            ]);
        }
    }
    
    protected function createValorantDenominations($gameId): void
    {
        $denominations = [
            ['name' => '420 VP', 'amount' => 420, 'price' => 45000, 'sort_order' => 1],
            ['name' => '700 VP', 'amount' => 700, 'price' => 75000, 'sort_order' => 2],
            ['name' => '1375 VP', 'amount' => 1375, 'price' => 145000, 'is_hot' => true, 'sort_order' => 3],
            ['name' => '2400 VP', 'amount' => 2400, 'price' => 250000, 'sort_order' => 4],
            ['name' => '4000 VP', 'amount' => 4000, 'price' => 415000, 'sort_order' => 5],
            ['name' => '8150 VP', 'amount' => 8150, 'price' => 835000, 'sort_order' => 6],
        ];

        foreach ($denominations as $denom) {
            Denomination::create([
                'game_id' => $gameId,
                'name' => $denom['name'],
                'amount' => $denom['amount'],
                'bonus' => 0,
                'price' => $denom['price'],
                'is_hot' => $denom['is_hot'] ?? false,
                'is_promo' => false,
                'is_active' => true,
                'sort_order' => $denom['sort_order'],
                'sku' => 'VAL-' . $denom['amount'],
            ]);
        }
    }
    
    protected function createCODDenominations($gameId): void
    {
        $denominations = [
            ['name' => '53 CP', 'amount' => 53, 'price' => 9900, 'sort_order' => 1],
            ['name' => '112 CP', 'amount' => 112, 'price' => 19900, 'sort_order' => 2],
            ['name' => '278 CP', 'amount' => 278, 'price' => 49500, 'is_hot' => true, 'sort_order' => 3],
            ['name' => '581 CP', 'amount' => 581, 'price' => 99000, 'sort_order' => 4],
            ['name' => '1373 CP', 'amount' => 1373, 'price' => 198000, 'sort_order' => 5],
            ['name' => '2395 CP', 'amount' => 2395, 'price' => 396000, 'sort_order' => 6],
        ];

        foreach ($denominations as $denom) {
            Denomination::create([
                'game_id' => $gameId,
                'name' => $denom['name'],
                'amount' => $denom['amount'],
                'bonus' => 0,
                'price' => $denom['price'],
                'is_hot' => $denom['is_hot'] ?? false,
                'is_promo' => false,
                'is_active' => true,
                'sort_order' => $denom['sort_order'],
                'sku' => 'COD-' . $denom['amount'],
            ]);
        }
    }
}