<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();

            // Optional: link to employee (nullable = "default/template" row)
            $table->foreignId('employee_id')->nullable()
                  ->constrained()->nullOnDelete();

            $table->string('name')->nullable();  // e.g. "Grade-1 Default"

            // Gross salary the user enters
            $table->decimal('gross_salary', 12, 2)->default(0);

            // Computed — stored for reporting
            $table->decimal('basic_salary', 12, 2)->default(0);      // (Gross - (Med+Conv+Food)) / 1.5
            $table->decimal('house_rent', 12, 2)->default(0);        // 50% × Basic

            // Allowances (fixed per the Bangladesh Labor Act 2025 reference)
            $table->decimal('medical_allowance', 10, 2)->default(750);
            $table->decimal('conveyance', 10, 2)->default(450);
            $table->decimal('food_allowance', 10, 2)->default(1250);

            // Derived
            $table->decimal('total_gross_salary', 12, 2)->default(0); // Basic + House + Med + Conv + Food
            $table->decimal('bonus', 12, 2)->default(0);              // configurable
            $table->string('bonus_note')->nullable();                 // e.g. "Festival bonus — 2025"

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
