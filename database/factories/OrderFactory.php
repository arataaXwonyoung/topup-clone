<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Game;
use App\Models\Denomination;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $game = Game::inRandomOrder()->first() ?? Game::factory()->create();
        $denomination = $game->denominations()->inRandomOrder()->first() 
            ?? Denomination::factory()->create(['game_id' => $game->id]);
        
        $subtotal = $denomination->price;
        $discount = fake()->boolean(30) ? fake()->numberBetween(5000, 50000) : 0;
        $fee = fake()->randomElement([1000, 1500, 2500]);
        $total = $subtotal - $discount + $fee;
        
        $status = fake()->randomElement(['PENDING', 'UNPAID', 'PAID', 'DELIVERED', 'EXPIRED']);
        
        return [
            'invoice_no' => 'TP' . strtoupper(fake()->bothify('????')) . date('YmdHis'),
            'user_id' => fake()->boolean(70) ? User::factory() : null,
            'game_id' => $game->id,
            'denomination_id' => $denomination->id,
            'account_id' => fake()->numerify('########'),
            'server_id' => $game->requires_server ? fake()->numerify('####') : null,
            'username' => fake()->userName(),
            'email' => fake()->safeEmail(),
            'whatsapp' => '62' . fake()->numerify('8##########'),
            'quantity' => 1,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'promo_code' => $discount > 0 ? fake()->randomElement(['NEWUSER10', 'WEEKEND15']) : null,
            'fee' => $fee,
            'total' => $total,
            'status' => $status,
            'expires_at' => in_array($status, ['PENDING', 'UNPAID']) ? now()->addHours(3) : null,
            'paid_at' => in_array($status, ['PAID', 'DELIVERED']) ? fake()->dateTimeBetween('-7 days') : null,
            'delivered_at' => $status === 'DELIVERED' ? fake()->dateTimeBetween('-7 days') : null,
            'delivery_data' => $status === 'DELIVERED' ? json_encode([
                'transaction_id' => 'TRX' . fake()->numerify('##########'),
                'status' => 'SUCCESS',
            ]) : null,
            'metadata' => [
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
            ],
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PENDING',
            'expires_at' => now()->addHours(3),
            'paid_at' => null,
            'delivered_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PAID',
            'paid_at' => now(),
            'expires_at' => null,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'DELIVERED',
            'paid_at' => fake()->dateTimeBetween('-7 days', '-1 hour'),
            'delivered_at' => now(),
            'delivery_data' => json_encode([
                'transaction_id' => 'TRX' . fake()->numerify('##########'),
                'status' => 'SUCCESS',
            ]),
        ]);
    }
}