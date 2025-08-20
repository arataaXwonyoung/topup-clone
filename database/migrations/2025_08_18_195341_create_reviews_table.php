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
        Schema::table('reviews', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('reviews', 'images')) {
                $table->json('images')->nullable()->after('comment');
            }
            
            if (!Schema::hasColumn('reviews', 'is_anonymous')) {
                $table->boolean('is_anonymous')->default(false)->after('is_approved');
            }
            
            if (!Schema::hasColumn('reviews', 'helpful_count')) {
                $table->integer('helpful_count')->default(0)->after('is_anonymous');
            }
            
            if (!Schema::hasColumn('reviews', 'helpful_users')) {
                $table->json('helpful_users')->nullable()->after('helpful_count');
            }
            
            if (!Schema::hasColumn('reviews', 'is_flagged')) {
                $table->boolean('is_flagged')->default(false)->after('helpful_users');
            }
            
            if (!Schema::hasColumn('reviews', 'metadata')) {
                $table->json('metadata')->nullable()->after('is_flagged');
            }
            
            // Add indexes
            $table->index('is_anonymous');
            $table->index('helpful_count');
            $table->index('is_flagged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'images',
                'is_anonymous', 
                'helpful_count',
                'helpful_users',
                'is_flagged',
                'metadata'
            ]);
        });
    }
};