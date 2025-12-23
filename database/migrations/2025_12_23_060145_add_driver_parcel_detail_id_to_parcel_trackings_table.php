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
        Schema::table('parcel_trackings', function (Blueprint $table) {
            if (! Schema::hasColumn('parcel_trackings', 'driverParcelDetailId')) {
                $table->unsignedBigInteger('driverParcelDetailId')->nullable()->after('driverParcelId');
                $table->index('driverParcelDetailId');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcel_trackings', function (Blueprint $table) {
            if (Schema::hasColumn('parcel_trackings', 'driverParcelDetailId')) {
                $table->dropIndex(['driverParcelDetailId']);
                $table->dropColumn('driverParcelDetailId');
            }
        });
    }
};
