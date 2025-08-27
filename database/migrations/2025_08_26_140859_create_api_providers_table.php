<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['payment', 'topup', 'validation', 'sms', 'email', 'other'])->default('payment');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->string('base_url');
            $table->text('api_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('webhook_url')->nullable();
            $table->json('configuration')->nullable();
            $table->json('supported_methods')->nullable();
            $table->integer('rate_limit')->nullable();
            $table->integer('timeout')->default(30);
            $table->integer('retry_attempts')->default(3);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index(['priority', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_providers');
    }
};
