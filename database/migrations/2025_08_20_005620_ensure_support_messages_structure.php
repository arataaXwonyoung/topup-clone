<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Buat tabel jika belum ada (optional; kalau kamu sudah punya, boleh di-skip)
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

                $table->index('ticket_id', 'support_messages_ticket_id_idx');
                $table->index(['is_staff', 'is_read'], 'support_messages_staff_read_idx');
            });
            return;
        }

        // 2) Lengkapi kolom bila kurang
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
        });

        // 3) Tambah indeks hanya jika belum ada
        $db = DB::connection()->getDatabaseName();

        $hasTicketIdx = !empty(DB::select(
            "SELECT 1 FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = 'support_messages' AND index_name = 'support_messages_ticket_id_idx' 
             LIMIT 1",
            [$db]
        ));

        if (!$hasTicketIdx && Schema::hasColumn('support_messages', 'ticket_id')) {
            Schema::table('support_messages', function (Blueprint $table) {
                $table->index('ticket_id', 'support_messages_ticket_id_idx');
            });
        }

        $hasStaffReadIdx = !empty(DB::select(
            "SELECT 1 FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = 'support_messages' AND index_name = 'support_messages_staff_read_idx' 
             LIMIT 1",
            [$db]
        ));

        if (!$hasStaffReadIdx && Schema::hasColumn('support_messages', 'is_staff') && Schema::hasColumn('support_messages', 'is_read')) {
            Schema::table('support_messages', function (Blueprint $table) {
                $table->index(['is_staff', 'is_read'], 'support_messages_staff_read_idx');
            });
        }
    }

    public function down(): void
    {
        // Rollback perubahan yang kita tambahkan (aman kalau index/kolomnya tidak ada)
        if (Schema::hasTable('support_messages')) {
            // Drop index jika ada
            $db = DB::connection()->getDatabaseName();

            $hasTicketIdx = !empty(DB::select(
                "SELECT 1 FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = 'support_messages' AND index_name = 'support_messages_ticket_id_idx' 
                 LIMIT 1",
                [$db]
            ));
            if ($hasTicketIdx) {
                Schema::table('support_messages', function (Blueprint $table) {
                    $table->dropIndex('support_messages_ticket_id_idx');
                });
            }

            $hasStaffReadIdx = !empty(DB::select(
                "SELECT 1 FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = 'support_messages' AND index_name = 'support_messages_staff_read_idx' 
                 LIMIT 1",
                [$db]
            ));
            if ($hasStaffReadIdx) {
                Schema::table('support_messages', function (Blueprint $table) {
                    $table->dropIndex('support_messages_staff_read_idx');
                });
            }

            // (opsional) drop kolom yang mungkin ditambahkan oleh migration ini
            Schema::table('support_messages', function (Blueprint $table) {
                foreach (['attachments','is_staff','is_system','is_read','read_at'] as $col) {
                    if (Schema::hasColumn('support_messages', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
