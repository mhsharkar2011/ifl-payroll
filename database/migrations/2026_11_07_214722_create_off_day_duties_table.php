<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('off_day_duties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->date('duty_date');
            $table->decimal('hours_worked', 5, 2)->default(8.00);

            // ✅ NEW: Track compensatory leave earned (Rule 4.4)
            $table->decimal('compensatory_leave_days', 5, 2)->default(0);

            $table->decimal('hourly_rate', 10, 2)->default(0); // Effective rate at the time of entry
            $table->decimal('total_amount', 10, 2)->default(0); // Total 2x wages

            $table->string('reason')->nullable(); // e.g., "Friday Duty", "Eid Holiday"
            $table->string('status')->default('pending'); // pending, approved

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Prevent logging the same employee on the same date twice
            $table->unique(['employee_id', 'duty_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('off_day_duties');
    }
};
