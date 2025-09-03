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
        Schema::table('attendances', function (Blueprint $table) {
            // GPS coordinates for location validation
            $table->decimal('latitude', 10, 8)->nullable()->after('clock_out');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('accuracy')->nullable()->after('longitude'); // accuracy in meters

            // Additional attendance tracking fields
            $table->string('action_type')->default('manual')->after('accuracy'); // manual, gps, qr, etc.
            $table->string('status')->default('present')->after('action_type'); // present, late, absent, etc.
            $table->text('location_notes')->nullable()->after('status'); // additional location info

            // Time tracking improvements
            $table->timestamp('check_in_at')->nullable()->after('location_notes');
            $table->timestamp('check_out_at')->nullable()->after('check_in_at');

            // Index for better performance
            $table->index(['employee_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'date']);
            $table->dropIndex(['date', 'status']);

            $table->dropColumn([
                'latitude',
                'longitude',
                'accuracy',
                'action_type',
                'status',
                'location_notes',
                'check_in_at',
                'check_out_at'
            ]);
        });
    }
};
