<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_admin' => false,
            'phone' => fake()->phoneNumber(),
            'whatsapp' => fake()->phoneNumber(),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'Indonesia',
            'balance' => fake()->randomFloat(2, 0, 1000000),
            'points' => fake()->numberBetween(0, 10000),
            'level' => fake()->randomElement(['bronze', 'silver', 'gold', 'platinum', 'diamond']),
            'is_active' => true,
            'is_verified' => fake()->boolean(70),
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by' => fake()->optional()->regexify('[A-Z]{8}'),
            'preferences' => [
                'newsletter' => fake()->boolean(),
                'notifications' => fake()->boolean(),
                'theme' => fake()->randomElement(['light', 'dark']),
            ],
            'last_login_at' => fake()->dateTimeThisMonth(),
            'last_login_ip' => fake()->ipv4(),
            'login_count' => fake()->numberBetween(0, 100),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the user is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Create a user with specific level
     */
    public function withLevel(string $level): static
    {
        $pointsMap = [
            'bronze' => 0,
            'silver' => 1500,
            'gold' => 6000,
            'platinum' => 15000,
            'diamond' => 60000,
        ];

        return $this->state(fn (array $attributes) => [
            'level' => $level,
            'points' => $pointsMap[$level] ?? 0,
        ]);
    }
}