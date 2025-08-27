<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Points & Rewards System
        Schema::create('user_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('points')->default(0);
            $table->string('tier')->default('bronze'); // bronze, silver, gold, diamond
            $table->integer('total_earned')->default(0);
            $table->integer('total_spent')->default(0);
            $table->timestamps();
        });

        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // earned, spent
            $table->integer('amount');
            $table->string('source'); // purchase, achievement, referral, daily_login, reward_redeem
            $table->string('description');
            $table->morphs('related'); // Related model (order, achievement, etc.)
            $table->timestamps();
        });

        // Achievements System
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // first_purchase, big_spender, etc.
            $table->string('name');
            $table->text('description');
            $table->string('category'); // purchases, loyalty, social, special
            $table->string('icon');
            $table->integer('points_reward')->default(0);
            $table->json('conditions'); // Achievement unlock conditions
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained()->onDelete('cascade');
            $table->json('progress')->nullable(); // Current progress towards achievement
            $table->boolean('is_unlocked')->default(false);
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'achievement_id']);
        });

        // Gaming Profile System
        Schema::create('user_gaming_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('gaming_level')->default(1);
            $table->integer('gaming_score')->default(0);
            $table->integer('experience_points')->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->integer('unique_games')->default(0);
            $table->integer('streak_days')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->json('favorite_games')->nullable(); // Game statistics
            $table->json('activity_patterns')->nullable(); // Peak hours, etc.
            $table->timestamps();
        });

        // Rewards System
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('type'); // game_credit, voucher, exclusive
            $table->string('category'); // games, vouchers, exclusive
            $table->integer('points_cost');
            $table->string('reward_code')->nullable(); // For vouchers
            $table->decimal('value', 10, 2)->nullable(); // Monetary value
            $table->json('metadata')->nullable(); // Additional reward data
            $table->integer('stock')->nullable(); // Limited rewards
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('reward_id')->constrained()->onDelete('cascade');
            $table->integer('points_spent');
            $table->string('redemption_code')->unique();
            $table->string('status')->default('pending'); // pending, delivered, expired
            $table->json('delivery_data')->nullable(); // Voucher codes, etc.
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Referral System
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade');
            $table->string('referral_code')->unique();
            $table->string('status')->default('pending'); // pending, completed
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('user_gaming_profiles');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('user_points');
    }
};