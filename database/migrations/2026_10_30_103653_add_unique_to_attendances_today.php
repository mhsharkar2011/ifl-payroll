<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances_today', function (Blueprint $table) {
            $table->unique(
                ['employee_id', 'timestamp', 'status'],
                'uniq_att_today_emp_time_status'
            );
        });
    }

    public function down(): void
    {
        Schema::table('attendances_today', function (Blueprint $table) {
            $table->dropUnique('uniq_att_today_emp_time_status');
        });
    }
};
