<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_commands', function (Blueprint $table) {
            if (!Schema::hasColumn('device_commands', 'return_code')) {
                $table->string('return_code', 32)->nullable()
                    ->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('device_commands', function (Blueprint $table) {
            $table->dropColumn('return_code');
        });
    }
};
