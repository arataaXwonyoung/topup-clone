<?php
// database/seeders/CreateAdminSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing admin
        User::where('email', 'admin@takapedia.com')->delete();
        
        // Create new admin
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@takapedia.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        echo "Admin created successfully!\n";
        echo "Email: admin@takapedia.com\n";
        echo "Password: admin123\n";
        echo "User ID: {$admin->id}\n";
        echo "Is Admin: " . ($admin->is_admin ? 'Yes' : 'No') . "\n";
    }
}