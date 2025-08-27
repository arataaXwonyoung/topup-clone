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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'is_suspended')) {
                $table->boolean('is_suspended')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'suspended_until')) {
                $table->timestamp('suspended_until')->nullable()->after('is_suspended');
            }
            if (!Schema::hasColumn('users', 'suspension_reason')) {
                $table->text('suspension_reason')->nullable()->after('suspended_until');
            }
            if (!Schema::hasColumn('users', 'daily_limit')) {
                $table->decimal('daily_limit', 15, 2)->nullable()->after('suspension_reason');
            }
            if (!Schema::hasColumn('users', 'monthly_limit')) {
                $table->decimal('monthly_limit', 15, 2)->nullable()->after('daily_limit');
            }
            if (!Schema::hasColumn('users', 'max_orders_per_day')) {
                $table->integer('max_orders_per_day')->default(10)->after('monthly_limit');
            }
            if (!Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('max_orders_per_day');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'is_active',
                'is_suspended',
                'suspended_until',
                'suspension_reason',
                'daily_limit',
                'monthly_limit',
                'max_orders_per_day',
                'notes'
            ]);
        });
    }
};
