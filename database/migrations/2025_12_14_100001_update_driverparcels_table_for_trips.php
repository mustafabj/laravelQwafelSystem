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
            $table->unsignedBigInteger('tripId')->nullable()->after('parcelId');
            // The actual date this driver parcel is associated with the trip
            $table->date('tripDate')->nullable()->after('tripId');
            $table->enum('status', ['pending', 'in_transit', 'arrived', 'delivered'])->default('pending')->after('tripDate');
            $table->timestamp('arrivedAt')->nullable()->after('status');
            $table->text('delayReason')->nullable()->after('arrivedAt');

            $table->foreign('tripId')->references('tripId')->on('trips')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driverparcels', function (Blueprint $table) {
            $table->dropForeign(['tripId']);
            $table->dropColumn(['tripId', 'tripDate', 'status', 'arrivedAt', 'delayReason']);
        });
    }
};

