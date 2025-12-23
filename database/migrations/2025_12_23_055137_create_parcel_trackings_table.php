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
        if (Schema::hasTable('parcel_trackings')) {
            return;
        }

        Schema::create('parcel_trackings', function (Blueprint $table) {
            $table->id();
            $table->integer('parcelId'); // Original parcel ID (matches parcels.parcelId - integer)
            $table->integer('driverParcelId')->nullable(); // Driver parcel ID (matches driverparcels.parcelId - integer)
            $table->unsignedBigInteger('tripId')->nullable(); // Trip ID (matches trips.tripId - bigint unsigned)
            $table->string('status'); // pending, in_transit, arrived, delivered
            $table->string('location')->nullable(); // Current location/office
            $table->text('description')->nullable(); // Description of the status change
            $table->timestamp('trackedAt'); // When this status was tracked
            $table->string('trackedBy')->nullable(); // Who/what tracked this (system, user, etc.)
            $table->timestamps();

            $table->index('parcelId');
            $table->index('driverParcelId');
            $table->index('tripId');
            $table->index('status');
            $table->index('trackedAt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_trackings');
    }
};
