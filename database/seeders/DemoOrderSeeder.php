<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use App\Models\Game;
use Illuminate\Database\Seeder;

class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo users
        $users = User::factory(10)->create();
        
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@takapedia.com',
            'is_admin' => true,
        ]);
        
        // Create test user
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        $games = Game::all();
        
        // Create orders for each game
        foreach ($games as $game) {
            // Create successful orders
            for ($i = 0; $i < rand(5, 15); $i++) {
                $order = Order::factory()->delivered()->create([
                    'user_id' => $users->random()->id,
                    'game_id' => $game->id,
                    'denomination_id' => $game->denominations->random()->id,
                ]);
                
                // Create payment record
                Payment::create([
                    'order_id' => $order->id,
                    'provider' => 'midtrans',
                    'method' => fake()->randomElement(['QRIS', 'VA', 'EWALLET']),
                    'channel' => fake()->randomElement(['bca', 'bni', 'gopay', 'dana']),
                    'reference' => 'MT-' . $order->invoice_no,
                    'status' => 'PAID',
                    'amount' => $order->total,
                    'paid_at' => $order->paid_at,
                ]);
                
                // Create review (50% chance)
                if (fake()->boolean(50)) {
                    Review::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'game_id' => $game->id,
                        'rating' => fake()->numberBetween(3, 5),
                        'comment' => fake()->randomElement([
                            'Proses cepat, recommended!',
                            'Top up lancar, terima kasih!',
                            'Pelayanan memuaskan, akan order lagi.',
                            'Harga terjangkau dan proses instant.',
                            'Sangat puas dengan layanannya!',
                        ]),
                        'is_verified' => true,
                        'is_approved' => true,
                    ]);
                }
            }
            
            // Create pending orders
            for ($i = 0; $i < rand(1, 3); $i++) {
                $order = Order::factory()->pending()->create([
                    'game_id' => $game->id,
                    'denomination_id' => $game->denominations->random()->id,
                ]);
                
                Payment::create([
                    'order_id' => $order->id,
                    'provider' => 'midtrans',
                    'method' => fake()->randomElement(['QRIS', 'VA', 'EWALLET']),
                    'reference' => 'MT-' . $order->invoice_no,
                    'status' => 'PENDING',
                    'amount' => $order->total,
                    'expires_at' => $order->expires_at,
                ]);
            }
        }
        
        // Create orders for test user
        for ($i = 0; $i < 5; $i++) {
            $game = $games->random();
            $order = Order::factory()->delivered()->create([
                'user_id' => $testUser->id,
                'game_id' => $game->id,
                'denomination_id' => $game->denominations->random()->id,
                'email' => $testUser->email,
            ]);
            
            Payment::create([
                'order_id' => $order->id,
                'provider' => 'midtrans',
                'method' => 'QRIS',
                'reference' => 'MT-' . $order->invoice_no,
                'status' => 'PAID',
                'amount' => $order->total,
                'paid_at' => $order->paid_at,
            ]);
        }
    }
}