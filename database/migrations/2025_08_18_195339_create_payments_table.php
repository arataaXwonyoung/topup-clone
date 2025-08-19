<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->enum('method', ['QRIS', 'VA', 'EWALLET', 'CC', 'CONVENIENCE']);
            $table->string('channel')->nullable();
            $table->string('reference')->unique()->index();
            $table->string('external_id')->nullable();
            $table->string('va_number')->nullable();
            $table->text('qris_string')->nullable();
            $table->string('checkout_url')->nullable();
            $table->enum('status', ['PENDING', 'PAID', 'EXPIRED', 'FAILED', 'REFUNDED'])->default('PENDING');
            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 12, 2)->default(0);
            $table->json('payload')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index('provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
