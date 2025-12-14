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
        Schema::create('trips', function (Blueprint $table) {
            $table->id('tripId');
            $table->string('tripName');
            $table->unsignedBigInteger('driverId');
            $table->integer('officeId');
            $table->string('destination');
            // Days of the week this trip runs (JSON array: ["monday", "wednesday", "friday"])
            $table->json('daysOfWeek');
            // Time for each day (JSON object: {"monday": "08:00", "wednesday": "08:00", "friday": "10:00"})
            $table->json('times');
            $table->boolean('isActive')->default(true);
            $table->text('notes')->nullable();
            $table->integer('createdBy');
            $table->timestamps();

            $table->foreign('driverId')->references('driverId')->on('driver')->onDelete('restrict');
            $table->foreign('officeId')->references('officeId')->on('office')->onDelete('restrict');
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};

