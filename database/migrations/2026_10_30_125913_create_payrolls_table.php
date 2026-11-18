<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_amount', 10, 2)->default(0);
            $table->decimal('off_day_hours', 8, 2)->default(0);
            $table->decimal('off_day_amount', 10, 2)->default(0);
            $table->decimal('total_earnings', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
