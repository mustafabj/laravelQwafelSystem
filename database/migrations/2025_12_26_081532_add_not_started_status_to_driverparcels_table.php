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
        // Modify the enum to include 'not_started'
        // MySQL doesn't support direct enum modification, so we need to use ALTER TABLE
        DB::statement("ALTER TABLE driverparcels MODIFY COLUMN status ENUM('pending', 'not_started', 'in_transit', 'arrived', 'delivered') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE driverparcels MODIFY COLUMN status ENUM('pending', 'in_transit', 'arrived', 'delivered') DEFAULT 'pending'");
    }
};
