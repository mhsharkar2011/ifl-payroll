<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances_today', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances_today', 'merged_at')) {
                $table->timestamp('merged_at')->nullable()->after('notes');
                $table->index('merged_at', 'idx_att_today_merged');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances_today', function (Blueprint $table) {
            $table->dropIndex('idx_att_today_merged');
            $table->dropColumn('merged_at');
        });
    }
};
