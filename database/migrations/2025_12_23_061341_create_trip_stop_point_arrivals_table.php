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
        Schema::dropIfExists('trip_stop_point_arrivals');

        Schema::create('trip_stop_point_arrivals', function (Blueprint $table) {
            $table->id('arrivalId');
            $table->integer('driverParcelId'); // Matches parcelId type in driverparcels table
            $table->unsignedBigInteger('stopPointId');
            $table->timestamp('arrivedAt')->nullable()->comment('When driver actually arrived');
            $table->timestamp('expectedArrivalTime')->nullable()->comment('Expected arrival time at this stop');
            $table->enum('status', ['pending', 'approved', 'rejected', 'auto_approved'])->default('pending');
            $table->boolean('onTime')->nullable()->comment('Whether driver arrived on time');
            $table->text('adminComment')->nullable()->comment('Comment visible to customer');
            $table->integer('approvedBy')->nullable(); // Matches users.id type (integer)
            $table->timestamp('approvedAt')->nullable();
            $table->timestamp('requestedAt')->nullable()->comment('When arrival was requested/notified');
            $table->timestamps();

            $table->foreign('driverParcelId')->references('parcelId')->on('driverparcels')->onDelete('cascade');
            $table->foreign('stopPointId')->references('stopPointId')->on('trip_stop_points')->onDelete('cascade');
            $table->foreign('approvedBy')->references('id')->on('users')->onDelete('set null');
            $table->index(['driverParcelId', 'stopPointId']);
            $table->index('status');
            $table->index('requestedAt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_stop_point_arrivals');
    }
};
