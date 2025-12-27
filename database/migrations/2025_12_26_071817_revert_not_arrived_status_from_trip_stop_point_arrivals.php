<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Revert any 'not_arrived' status back to 'approved' (since delays are approved with delay info)
        DB::statement("UPDATE trip_stop_point_arrivals SET status = 'approved' WHERE status = 'not_arrived'");

        // Update enum to remove 'not_arrived' (keep original values: pending, approved, rejected, auto_approved)
        DB::statement("ALTER TABLE trip_stop_point_arrivals MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'auto_approved') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add 'not_arrived' to enum if needed
        DB::statement("ALTER TABLE trip_stop_point_arrivals MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'not_arrived', 'auto_approved') DEFAULT 'pending'");
    }
};
