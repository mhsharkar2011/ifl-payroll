<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            if (!Schema::hasColumn('backups', 'last_action')) {
                $table->string('last_action')->nullable()->after('status');
            }
            if (!Schema::hasColumn('backups', 'status_message')) {
                $table->text('status_message')->nullable()->after('last_action');
            }
            if (!Schema::hasColumn('backups', 'last_action_at')) {
                $table->timestamp('last_action_at')->nullable()->after('status_message');
            }
            if (!Schema::hasColumn('backups', 'restored_at')) {
                $table->timestamp('restored_at')->nullable()->after('last_action_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropColumn(['last_action', 'status_message', 'last_action_at', 'restored_at']);
        });
    }
};
