<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update Admin User
        User::updateOrCreate(
            ['email' => 'admin@takapedia.com'], // kondisi unik
            [
                'name'              => 'Admin',
                'password'          => Hash::make('password123'),
                'is_admin'          => true,
                'is_active'         => true,
                'is_verified'       => true,
                'email_verified_at' => now(),
                'phone'             => '081234567890',
                'whatsapp'          => '081234567890',
            ]
        );

        // Create or update Test User
        User::updateOrCreate(
            ['email' => 'user@takapedia.com'],
            [
                'name'              => 'Test User',
                'password'          => Hash::make('password123'),
                'is_admin'          => false,
                'is_active'         => true,
                'is_verified'       => true,
                'email_verified_at' => now(),
                'phone'             => '081234567891',
                'whatsapp'          => '081234567891',
                'balance'           => 100000,
                'points'            => 500,
                'level'             => 'silver',
            ]
        );
    }
}
