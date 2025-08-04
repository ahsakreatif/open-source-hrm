<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_recaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->integer('total_days_present')->default(0);
            $table->decimal('total_hours_worked', 8, 2)->default(0);
            $table->integer('total_days_absent')->default(0);
            $table->integer('total_days_leave')->default(0);
            $table->decimal('total_leave_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_departure_minutes')->default(0);
            $table->integer('working_days_in_month')->default(0);
            $table->decimal('attendance_rate', 5, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'error'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate recaps for same employee/month
            $table->unique(['employee_id', 'year', 'month'], 'unique_employee_month_recap');

            // Indexes for better performance
            $table->index(['employee_id', 'year', 'month']);
            $table->index(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_recaps');
    }
};
