<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');                    // backup_2026_09_30_123456.sql
            $table->string('disk')->default('local');      // storage disk
            $table->string('path');                        // full path
            $table->bigInteger('size')->default(0);        // bytes
            $table->string('type')->default('manual');     // manual/auto
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
