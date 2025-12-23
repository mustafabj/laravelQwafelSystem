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
        Schema::table('driverparceldetails', function (Blueprint $table) {
            // Link to the main parcel detail
            if (! Schema::hasColumn('driverparceldetails', 'parcelDetailId')) {
                $table->integer('parcelDetailId')->nullable()->after('detailId');
            }
            // Quantity taken from the parcel detail (can be less than parcel detail quantity)
            if (! Schema::hasColumn('driverparceldetails', 'quantityTaken')) {
                $table->integer('quantityTaken')->default(0)->after('parcelDetailId');
            }
            // Track arrival status
            if (! Schema::hasColumn('driverparceldetails', 'isArrived')) {
                $table->boolean('isArrived')->default(false)->after('quantityTaken');
            }
            if (! Schema::hasColumn('driverparceldetails', 'arrivedAt')) {
                $table->timestamp('arrivedAt')->nullable()->after('isArrived');
            }
            // Keep the old fields for backward compatibility but make them nullable
            if (Schema::hasColumn('driverparceldetails', 'detailQun')) {
                $table->integer('detailQun')->nullable()->change();
            }
            if (Schema::hasColumn('driverparceldetails', 'detailInfo')) {
                $table->string('detailInfo')->nullable()->change();
            }
        });

        // Add foreign key if it doesn't exist and column exists
        if (Schema::hasColumn('driverparceldetails', 'parcelDetailId')) {
            Schema::table('driverparceldetails', function (Blueprint $table) {
                $foreignKeys = Schema::getConnection()
                    ->select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'driverparceldetails' AND CONSTRAINT_NAME = 'driverparceldetails_parceldetailid_foreign'");

                if (empty($foreignKeys)) {
                    $table->foreign('parcelDetailId')->references('detailId')->on('parcelsdetails')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driverparceldetails', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('driverparceldetails', 'parcelDetailId')) {
                try {
                    $table->dropForeign(['parcelDetailId']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $columnsToDrop[] = 'parcelDetailId';
            }
            if (Schema::hasColumn('driverparceldetails', 'quantityTaken')) {
                $columnsToDrop[] = 'quantityTaken';
            }
            if (Schema::hasColumn('driverparceldetails', 'isArrived')) {
                $columnsToDrop[] = 'isArrived';
            }
            if (Schema::hasColumn('driverparceldetails', 'arrivedAt')) {
                $columnsToDrop[] = 'arrivedAt';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            if (Schema::hasColumn('driverparceldetails', 'detailQun')) {
                $table->integer('detailQun')->nullable(false)->change();
            }
            if (Schema::hasColumn('driverparceldetails', 'detailInfo')) {
                $table->string('detailInfo')->nullable(false)->change();
            }
        });
    }
};
