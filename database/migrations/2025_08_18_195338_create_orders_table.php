<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('game_id')->constrained();
            $table->foreignId('denomination_id')->constrained();
            $table->string('account_id');
            $table->string('server_id')->nullable();
            $table->string('username')->nullable();
            $table->string('email');
            $table->string('whatsapp');
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('promo_code')->nullable();
            $table->decimal('fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('status', ['PENDING', 'UNPAID', 'PAID', 'EXPIRED', 'FAILED', 'DELIVERED', 'REFUNDED'])
                  ->default('PENDING');
            $table->datetime('expires_at')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->string('delivery_data')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index(['email', 'whatsapp']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
