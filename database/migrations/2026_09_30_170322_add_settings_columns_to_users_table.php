<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone'))         $table->string('phone', 30)->nullable()->after('email');
            if (!Schema::hasColumn('users', 'timezone'))      $table->string('timezone', 64)->default('Asia/Dhaka')->after('phone');
            if (!Schema::hasColumn('users', 'language'))      $table->string('language', 8)->default('en')->after('timezone');
            if (!Schema::hasColumn('users', 'theme'))         $table->string('theme', 16)->default('light')->after('language');
            if (!Schema::hasColumn('users', 'notify_email'))  $table->boolean('notify_email')->default(true)->after('theme');
            if (!Schema::hasColumn('users', 'notify_push'))   $table->boolean('notify_push')->default(false)->after('notify_email');
            if (!Schema::hasColumn('users', 'notify_weekly')) $table->boolean('notify_weekly')->default(true)->after('notify_push');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'timezone', 'language', 'theme',
                'notify_email', 'notify_push', 'notify_weekly',
            ]);
        });
    }
};
