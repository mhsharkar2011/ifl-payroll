<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_banks', function (Blueprint $table) {
            $table->id();
            // Creates the foreign key linking to the employees table
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_banks');
    }
};
