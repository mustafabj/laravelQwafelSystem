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
        if (Schema::hasTable('driverparceldetails')) {
            return;
        }

        Schema::create('driverparceldetails', function (Blueprint $table) {
            $table->integer('detailId', true);
            $table->integer('detailQun');
            $table->string('detailInfo');
            $table->integer('parcelId');
            // $table->foreign('parcelId')->references('parcelId')->on('parcels')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driverparceldetails');
    }
};
