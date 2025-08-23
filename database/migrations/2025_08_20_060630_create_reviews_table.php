<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->json('helpful_users')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('order_id');
            $table->index('user_id');
            $table->index('game_id');
            $table->index('is_approved');
            $table->index('is_anonymous');
            $table->index('helpful_count');
            $table->index('is_flagged');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
