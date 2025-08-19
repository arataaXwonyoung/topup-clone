<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Indonesia');
            $table->string('avatar')->nullable();
            $table->decimal('balance', 12, 2)->default(0);
            $table->integer('points')->default(0);
            $table->enum('level', ['bronze', 'silver', 'gold', 'platinum', 'diamond'])->default('bronze');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->string('referral_code')->unique()->nullable();
            $table->string('referred_by')->nullable();
            $table->json('preferences')->nullable();
            $table->datetime('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->integer('login_count')->default(0);
            $table->rememberToken();
            $table->timestamps();

            $table->index('email');
            $table->index('phone');
            $table->index('whatsapp');
            $table->index('is_admin');
            $table->index('is_active');
            $table->index('level');
            $table->index('referral_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
