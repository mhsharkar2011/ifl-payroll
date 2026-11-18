<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->string('device_serial')->index();
            $table->string('command');
            $table->text('payload')->nullable();
            $table->enum('status', ['pending', 'sent', 'completed', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['device_serial', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_commands');
    }
};
