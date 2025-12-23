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
        Schema::table('driverparcels', function (Blueprint $table) {
            if (! Schema::hasColumn('driverparcels', 'tripId')) {
                $table->unsignedBigInteger('tripId')->nullable()->after('parcelId'); // Matches trips.tripId (bigint unsigned)
            }
            // The actual date this driver parcel is associated with the trip
            if (! Schema::hasColumn('driverparcels', 'tripDate')) {
                $table->date('tripDate')->nullable()->after('tripId');
            }
            if (! Schema::hasColumn('driverparcels', 'status')) {
                $table->enum('status', ['pending', 'in_transit', 'arrived', 'delivered'])->default('pending')->after('tripDate');
            }
            if (! Schema::hasColumn('driverparcels', 'arrivedAt')) {
                $table->timestamp('arrivedAt')->nullable()->after('status');
            }
            if (! Schema::hasColumn('driverparcels', 'delayReason')) {
                $table->text('delayReason')->nullable()->after('arrivedAt');
            }
        });

        // Add foreign key if it doesn't exist
        if (Schema::hasColumn('driverparcels', 'tripId') && Schema::hasTable('trips')) {
            Schema::table('driverparcels', function (Blueprint $table) {
                $foreignKeys = Schema::getConnection()
                    ->select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'driverparcels' AND CONSTRAINT_NAME = 'driverparcels_tripid_foreign'");

                if (empty($foreignKeys)) {
                    $table->foreign('tripId')->references('tripId')->on('trips')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driverparcels', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('driverparcels', 'tripId')) {
                // Drop foreign key first
                try {
                    $table->dropForeign(['tripId']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $columnsToDrop[] = 'tripId';
            }
            if (Schema::hasColumn('driverparcels', 'tripDate')) {
                $columnsToDrop[] = 'tripDate';
            }
            if (Schema::hasColumn('driverparcels', 'status')) {
                $columnsToDrop[] = 'status';
            }
            if (Schema::hasColumn('driverparcels', 'arrivedAt')) {
                $columnsToDrop[] = 'arrivedAt';
            }
            if (Schema::hasColumn('driverparcels', 'delayReason')) {
                $columnsToDrop[] = 'delayReason';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
