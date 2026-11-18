<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // ---- User account link (nullable for HR-only records) ----
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // ---- Identity ----
            $table->string('employee_id')->unique()
                ->comment('HR-assigned employee code, e.g. 150026');

            $table->string('device_user_id', 64)->nullable()->unique()
                ->comment('PIN used by ZKTeco device — falls back to employee_id if null');

            $table->string('first_name');
            $table->string('last_name');

            // ---- Contact ----
            $table->string('email')->unique();
            $table->string('phone', 20);           // phone numbers fit in 20 chars

            // ---- Org structure (foreign keys, not strings) ----
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('designation_id')
                ->nullable()
                ->constrained('designations')
                ->nullOnDelete();

            // ---- Employment ----
            $table->decimal('salary', 12, 2)
                ->comment('Base monthly salary');

            $table->enum('status', ['active', 'inactive', 'terminated'])
                ->default('active');

            $table->date('joining_date');
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();

            // ---- Media ----
            // Single canonical column for avatars.
            // The DashboardController and Vue pages expect this name.
            $table->string('avatar')->nullable()
                ->comment('Relative path under storage/app/public');

            $table->timestamps();

            // ---- Indexes ----
            $table->index('status',              'idx_emp_status');
            $table->index('department_id',       'idx_emp_department');
            $table->index('designation_id',      'idx_emp_designation');
            $table->index(['last_name', 'first_name'], 'idx_emp_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
