<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('author')->nullable();
            $table->enum('category', ['tips', 'promo', 'news', 'guide'])->default('tips');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->datetime('published_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
