<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('reference')->nullable()->index();
            $table->string('event_type')->nullable();
            $table->string('signature')->nullable();
            $table->json('headers')->nullable();
            $table->json('raw_payload');
            $table->datetime('processed_at')->nullable();
            $table->enum('status', ['PENDING', 'PROCESSED', 'FAILED', 'IGNORED'])->default('PENDING');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
