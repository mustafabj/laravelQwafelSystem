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
        if (Schema::hasTable('driverparcels')) {
            return;
        }

        Schema::create('driverparcels', function (Blueprint $table) {
            $table->integer('parcelId', true);
            $table->integer('parcelNumber');
            $table->string('driverName');
            $table->string('parcelDate');
            $table->string('cost');
            $table->string('paid');
            $table->string('costRest', 25);
            $table->string('driverNumber', 25);
            $table->string('currency', 10);
            $table->integer('userId');
            $table->string('sendTo');
            $table->integer('officeId');
            $table->string('token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driverparcels');
    }
};
