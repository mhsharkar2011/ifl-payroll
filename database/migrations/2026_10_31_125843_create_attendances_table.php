<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // 1. The standard database relationship (e.g., ID = 1)
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            // 2. The string relationship (e.g., Employee ID = '150016')
            $table->string('employee_code', 64)->nullable();
            $table->foreign('employee_code')
                ->references('employee_id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamp('timestamp');

            $table->enum('status', [
                'check_in',
                'check_out',
                'break_in',
                'break_out',
                'overtime_in',
                'overtime_out',
            ])->comment('Punch direction for the timestamp');

            $table->unsignedTinyInteger('verified_by')
                ->default(0)
                ->comment('0=Manual, 1=Fingerprint, 2=Face, 3=Card');

            $table->string('device_serial', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Auto-calculated times based on 12:00 PM cutoff
            $table->time('in_time')->nullable()->comment('00:00 to 12:00');
            $table->time('out_time')->nullable()->comment('12:01 to 23:59');

            // Indexes
            $table->index(['employee_id', 'timestamp'], 'idx_att_emp_time');
            $table->index(['employee_code', 'timestamp'], 'idx_att_emp_code_time'); // Added index for the new column
            $table->index(['status', 'timestamp'], 'idx_att_status_time');
            $table->index('timestamp', 'idx_att_time');

            $table->unique(
                ['employee_id', 'timestamp', 'status'],
                'uniq_att_emp_time_status'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
