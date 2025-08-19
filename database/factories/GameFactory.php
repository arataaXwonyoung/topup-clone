<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        
        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'publisher' => fake()->company(),
            'cover_path' => '/images/games/default.jpg',
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['games', 'voucher', 'pulsa', 'entertainment']),
            'is_hot' => fake()->boolean(30),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
            'requires_server' => fake()->boolean(40),
            'id_label' => 'User ID',
            'server_label' => 'Server ID',
            'metadata' => [
                'rating' => fake()->randomFloat(1, 3.5, 5.0),
                'downloads' => fake()->numberBetween(1000, 1000000),
            ],
        ];
    }

    public function hot(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hot' => true,
        ]);
    }

    public function withServer(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_server' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
