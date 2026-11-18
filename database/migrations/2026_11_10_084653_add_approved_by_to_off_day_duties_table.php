<?php

// database/migrations/xxxx_add_approved_by_to_off_day_duties_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('off_day_duties', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('off_day_duties', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn('approved_by');
        });
    }
};
