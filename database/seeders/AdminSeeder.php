<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@takapedia.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'is_active' => true,
                'is_verified' => true,
                'phone' => '081234567890',
                'whatsapp' => '081234567890',
                'balance' => 0,
                'points' => 100000,
                'level' => 'diamond',
            ]
        );
        
        echo "Admin created: {$admin->email} / admin123\n";

        // Create Demo Admin
        $demo = User::updateOrCreate(
            ['email' => 'demo@admin.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('demo123'),
                'is_admin' => true,
                'is_active' => true,
                'is_verified' => true,
                'phone' => '081234567892',
                'whatsapp' => '081234567892',
                'balance' => 1000000,
                'points' => 25000,
                'level' => 'platinum',
            ]
        );
        
        echo "Demo Admin created: {$demo->email} / demo123\n";
    }
}