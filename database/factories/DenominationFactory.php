<?php

namespace Database\Factories;

use App\Models\Denomination;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

class DenominationFactory extends Factory
{
    protected $model = Denomination::class;

    public function definition(): array
    {
        $amount = fake()->randomElement([50, 100, 200, 300, 500, 1000, 2000, 5000]);
        $price = $amount * fake()->numberBetween(250, 350);
        $bonus = fake()->boolean(30) ? fake()->numberBetween(5, 20) : 0;

        return [
            'game_id' => Game::factory(),
            'name' => $amount . ' Diamonds',
            'amount' => $amount,
            'bonus' => $bonus,
            'price' => $price,
            'original_price' => fake()->boolean(20) ? $price * 1.2 : null,
            'is_hot' => fake()->boolean(20),
            'is_promo' => fake()->boolean(15),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
            'sku' => 'SKU-' . strtoupper(fake()->bothify('??##??##')),
            'metadata' => [
                'provider' => fake()->randomElement(['official', 'third_party']),
                'delivery_time' => fake()->randomElement(['instant', '1-5 minutes', '5-10 minutes']),
            ],
        ];
    }

    public function hot(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hot' => true,
        ]);
    }

    public function promo(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_promo' => true,
            'original_price' => $attributes['price'] * 1.3,
        ]);
    }
}