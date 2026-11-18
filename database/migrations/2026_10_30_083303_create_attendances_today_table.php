<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances_today', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->timestamp('timestamp');
            $table->enum('status', ['check_in', 'check_out', 'break_in', 'break_out', 'overtime_in', 'overtime_out']);
            $table->integer('verified_by')->default(0);
            $table->string('device_serial')->nullable();
            $table->string('ip_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->date('attendance_date')->index(); // Fast date filtering
            $table->timestamps();

            // Prevent duplicate: same employee + same timestamp + same status
            $table->unique(['employee_id', 'timestamp', 'status'], 'unique_today_attendance');

            // Fast lookups
            $table->index(['attendance_date', 'status']);
            $table->index(['employee_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances_today');
    }
};
