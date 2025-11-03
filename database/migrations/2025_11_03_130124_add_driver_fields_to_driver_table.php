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
        Schema::create('driver', function (Blueprint $table) {
            $table->id('driverId');
            $table->string('driverName', 100);
            $table->string('driverPhone', 50)->nullable();
            $table->string('driverEmail', 100)->nullable();
            $table->string('licenseNumber', 100)->nullable();
            $table->string('vehicleNumber', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver');
    }
};
