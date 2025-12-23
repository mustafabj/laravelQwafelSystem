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
        if (Schema::hasTable('tickets')) {
            return;
        }

        Schema::create('tickets', function (Blueprint $table) {
            $table->integer('ticketId', true);
            $table->integer('tecketNumber');
            $table->integer('customerId');
            $table->string('cost');
            $table->string('costRest', 25);
            $table->string('paid');
            $table->string('destination');
            $table->string('Seat');
            $table->string('travelDate');
            $table->string('travelTime');
            $table->string('ticketDate');
            $table->string('custNumber', 25);
            $table->string('currency', 10);
            $table->integer('userId');
            $table->integer('addressId');
            $table->string('accept', 10);
            $table->integer('officeId');
            $table->string('token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
