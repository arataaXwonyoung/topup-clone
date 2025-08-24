<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if reviews table exists
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('game_id')->constrained()->cascadeOnDelete();
                $table->integer('rating');
                $table->text('comment');
                $table->json('images')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->boolean('is_approved')->default(false);
                $table->boolean('is_anonymous')->default(false);
                $table->integer('helpful_count')->default(0);
                $table->json('helpful_users')->nullable();
                $table->boolean('is_flagged')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index(['game_id', 'is_approved']);
                $table->index(['user_id']);
                $table->index('is_anonymous');
                $table->index('helpful_count');
                $table->index('is_flagged');
            });
        } else {
            // Add missing columns if table exists
            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'order_id')) {
                    $table->foreignId('order_id')->after('id')->constrained()->cascadeOnDelete();
                }
                if (!Schema::hasColumn('reviews', 'user_id')) {
                    $table->foreignId('user_id')->after('order_id')->constrained()->cascadeOnDelete();
                }
                if (!Schema::hasColumn('reviews', 'game_id')) {
                    $table->foreignId('game_id')->after('user_id')->constrained()->cascadeOnDelete();
                }
                if (!Schema::hasColumn('reviews', 'rating')) {
                    $table->integer('rating')->after('game_id');
                }
                if (!Schema::hasColumn('reviews', 'comment')) {
                    $table->text('comment')->after('rating');
                }
                if (!Schema::hasColumn('reviews', 'is_verified')) {
                    $table->boolean('is_verified')->default(false)->after('comment');
                }
                if (!Schema::hasColumn('reviews', 'is_approved')) {
                    $table->boolean('is_approved')->default(false)->after('is_verified');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};