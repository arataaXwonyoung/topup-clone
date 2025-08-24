<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupApplication extends Command
{
    protected $signature = 'app:setup {--fresh : Fresh install with database reset}';
    protected $description = 'Setup the application with initial data';

    public function handle()
    {
        $this->info('🚀 Starting Takapedia Clone Setup...');
        
        // Check if fresh install
        if ($this->option('fresh')) {
            if ($this->confirm('This will delete all existing data. Are you sure?')) {
                $this->info('Dropping all tables...');
                Artisan::call('migrate:fresh');
                $this->info('✅ Database reset complete');
            } else {
                $this->info('Setup cancelled.');
                return;
            }
        }
        
        // Run migrations
        $this->info('Running migrations...');
        Artisan::call('migrate');
        $this->info('✅ Migrations complete');
        
        // Run seeders
        $this->info('Seeding database...');
        Artisan::call('db:seed');
        $this->info('✅ Database seeded');
        
        // Create storage link
        $this->info('Creating storage link...');
        Artisan::call('storage:link');
        $this->info('✅ Storage link created');
        
        // Clear cache
        $this->info('Clearing cache...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        $this->info('✅ Cache cleared');
        
        // Optimize
        $this->info('Optimizing application...');
        Artisan::call('optimize');
        $this->info('✅ Application optimized');
        
        $this->newLine();
        $this->info('🎉 Setup Complete!');
        $this->newLine();
        $this->table(
            ['Account Type', 'Email', 'Password'],
            [
                ['Admin', 'admin@takapedia.com', 'password123'],
                ['User', 'user@takapedia.com', 'password123'],
            ]
        );
        $this->newLine();
        $this->info('You can now access:');
        $this->info('- Frontend: ' . config('app.url'));
        $this->info('- Admin Panel: ' . config('app.url') . '/admin');
        $this->newLine();
    }
}