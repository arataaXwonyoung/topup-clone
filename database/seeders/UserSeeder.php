<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@takapedia.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
            'phone' => '081234567890',
            'whatsapp' => '081234567890',
            'is_active' => true,
            'is_verified' => true,
            'balance' => 0,
            'points' => 100000,
            'level' => 'diamond',
        ]);

        // Create Test User
        User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'phone' => '081234567891',
            'whatsapp' => '081234567891',
            'is_active' => true,
            'is_verified' => true,
            'balance' => 500000,
            'points' => 1500,
            'level' => 'silver',
        ]);

        // Create Demo Admin
        User::create([
            'name' => 'Demo Admin',
            'email' => 'demo@admin.com',
            'password' => Hash::make('demo123'),
            'is_admin' => true,
            'phone' => '081234567892',
            'whatsapp' => '081234567892',
            'is_active' => true,
            'is_verified' => true,
            'balance' => 1000000,
            'points' => 25000,
            'level' => 'platinum',
        ]);

        // Create Random Users
        User::factory()->count(10)->create();
        
        // Create some admin users
        User::factory()->admin()->count(2)->create();
        
        // Create some inactive users
        User::factory()->inactive()->count(3)->create();
        
        // Create users with different levels
        User::factory()->withLevel('bronze')->count(5)->create();
        User::factory()->withLevel('silver')->count(3)->create();
        User::factory()->withLevel('gold')->count(2)->create();
        User::factory()->withLevel('platinum')->count(1)->create();
    }
}