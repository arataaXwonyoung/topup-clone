<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create support_tickets table first
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number')->unique();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->enum('category', ['payment', 'delivery', 'account', 'refund', 'technical', 'other'])->default('other');
                $table->string('subject');
                $table->enum('status', ['open', 'pending', 'resolved', 'closed'])->default('open');
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->integer('rating')->nullable();
                $table->text('feedback')->nullable();
                $table->json('metadata')->nullable();
                $table->datetime('closed_at')->nullable();
                $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->datetime('reopened_at')->nullable();
                $table->datetime('rated_at')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index(['user_id', 'status']);
                $table->index('ticket_number');
                $table->index('status');
                $table->index('priority');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};