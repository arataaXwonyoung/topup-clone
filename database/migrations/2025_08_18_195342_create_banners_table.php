<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_path');
            $table->string('link')->nullable();
            $table->enum('position', ['hero', 'sidebar', 'footer'])->default('hero');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->datetime('starts_at')->nullable();
            $table->datetime('ends_at')->nullable();
            $table->timestamps();

            $table->index(['position', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
