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
        Schema::table('games', function (Blueprint $table) {
            if (!Schema::hasColumn('games', 'digiflazz_code')) {
                $table->string('digiflazz_code')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('games', 'enable_validation')) {
                $table->boolean('enable_validation')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('games', 'id_label')) {
                $table->string('id_label')->default('User ID')->after('description');
            }
            if (!Schema::hasColumn('games', 'requires_server')) {
                $table->boolean('requires_server')->default(false)->after('id_label');
            }
            if (!Schema::hasColumn('games', 'server_label')) {
                $table->string('server_label')->default('Server')->after('requires_server');
            }
            if (!Schema::hasColumn('games', 'validation_instructions')) {
                $table->text('validation_instructions')->nullable()->after('server_label');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'digiflazz_code',
                'enable_validation',
                'id_label',
                'requires_server',
                'server_label',
                'validation_instructions'
            ]);
        });
    }
};
