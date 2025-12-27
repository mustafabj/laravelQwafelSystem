<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trip_stop_point_arrivals', function (Blueprint $table) {
            // Add delay fields
            if (! Schema::hasColumn('trip_stop_point_arrivals', 'delayReason')) {
                $table->text('delayReason')->nullable()->after('adminComment');
            }
            if (! Schema::hasColumn('trip_stop_point_arrivals', 'delayDuration')) {
                $table->integer('delayDuration')->nullable()->comment('Delay duration in minutes')->after('delayReason');
            }

            // Update status enum: replace 'rejected' with 'not_arrived'
            // First, update existing 'rejected' records to 'not_arrived'
            DB::statement("UPDATE trip_stop_point_arrivals SET status = 'not_arrived' WHERE status = 'rejected'");

            // Drop the old enum and create new one
            DB::statement("ALTER TABLE trip_stop_point_arrivals MODIFY COLUMN status ENUM('pending', 'approved', 'not_arrived', 'auto_approved') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_stop_point_arrivals', function (Blueprint $table) {
            // Revert status enum
            DB::statement("UPDATE trip_stop_point_arrivals SET status = 'rejected' WHERE status = 'not_arrived'");
            DB::statement("ALTER TABLE trip_stop_point_arrivals MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'auto_approved') DEFAULT 'pending'");

            // Remove delay fields
            if (Schema::hasColumn('trip_stop_point_arrivals', 'delayDuration')) {
                $table->dropColumn('delayDuration');
            }
            if (Schema::hasColumn('trip_stop_point_arrivals', 'delayReason')) {
                $table->dropColumn('delayReason');
            }
        });
    }
};
