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
        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'driverId')) {
                // Drop foreign key first if it exists
                try {
                    $foreignKeys = Schema::getConnection()
                        ->select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'trips' AND CONSTRAINT_NAME LIKE '%driverid%'");
                    if (! empty($foreignKeys)) {
                        $table->dropForeign(['driverId']);
                    }
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->dropColumn('driverId');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->unsignedBigInteger('driverId')->after('tripName');
            $table->foreign('driverId')->references('driverId')->on('driver')->onDelete('restrict');
        });
    }
};
