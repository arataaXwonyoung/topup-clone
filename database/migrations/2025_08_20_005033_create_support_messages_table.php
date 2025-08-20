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
    if (!Schema::hasTable('support_messages')) {
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->boolean('is_staff')->default(false);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('ticket_id');
            $table->index(['is_staff', 'is_read']);
        });
    } else {
        // (opsional) pastikan kolom/index ada kalau tabelnya dibuat manual/versi lama
        Schema::table('support_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('support_messages', 'attachments')) {
                $table->json('attachments')->nullable()->after('message');
            }
            if (!Schema::hasColumn('support_messages', 'is_staff')) {
                $table->boolean('is_staff')->default(false)->after('attachments');
            }
            if (!Schema::hasColumn('support_messages', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('is_staff');
            }
            if (!Schema::hasColumn('support_messages', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('is_system');
            }
            if (!Schema::hasColumn('support_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
            if (Schema::hasColumn('support_messages', 'ticket_id')) {
                $table->index('ticket_id', 'support_messages_ticket_id_idx');
            }
            if (Schema::hasColumn('support_messages', 'is_staff') && Schema::hasColumn('support_messages', 'is_read')) {
                $table->index(['is_staff', 'is_read'], 'support_messages_staff_read_idx');
            }
        });
    }
}};