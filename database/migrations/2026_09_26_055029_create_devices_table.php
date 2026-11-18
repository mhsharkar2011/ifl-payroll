<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address');
            $table->integer('port')->default(4370);
            $table->integer('device_id')->default(1);
            $table->integer('password')->default(0);
            $table->integer('timeout')->default(30);
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();
            $table->string('platform')->nullable();
            $table->string('version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            // $table->timestamp('last_seen_at')->nullable()->after('last_sync_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('is_active');
            // $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
