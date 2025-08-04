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
        Schema::table('payrolls', function (Blueprint $table) {
            // Drop the existing enum and recreate it with the new value
            $table->enum('status', ['pending', 'completed', 'cancelled', 'calculated'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->change();
        });
    }
};
