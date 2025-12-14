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
        Schema::create('trip_stop_points', function (Blueprint $table) {
            $table->id('stopPointId');
            $table->unsignedBigInteger('tripId');
            $table->string('stopName');
            $table->time('arrivalTime');
            $table->integer('order')->default(0)->comment('Order of stop points in the trip');
            $table->timestamps();

            $table->foreign('tripId')->references('tripId')->on('trips')->onDelete('cascade');
            $table->index(['tripId', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_stop_points');
    }
};
