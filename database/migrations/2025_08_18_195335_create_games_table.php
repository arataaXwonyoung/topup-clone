<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->string('publisher')->nullable();
            $table->string('cover_path')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->default('games');
            $table->boolean('is_hot')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->boolean('requires_server')->default(false);
            $table->string('id_label')->default('User ID');
            $table->string('server_label')->nullable()->default('Server');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'category']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
