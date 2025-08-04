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
            $table->foreignId('attendance_recap_id')->nullable()->constrained('attendance_recaps')->onDelete('set null');
            $table->index('attendance_recap_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['attendance_recap_id']);
            $table->dropIndex(['attendance_recap_id']);
            $table->dropColumn('attendance_recap_id');
        });
    }
};
