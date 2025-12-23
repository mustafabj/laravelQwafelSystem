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
        if (Schema::hasTable('parcels')) {
            return;
        }

        Schema::create('parcels', function (Blueprint $table) {
            $table->integer('parcelId', true);
            $table->integer('parcelNumber');
            $table->integer('customerId');
            $table->string('parcelDate');
            $table->string('recipientName');
            $table->string('recipientNumber');
            $table->string('sendTo');
            $table->string('cost');
            $table->string('paid');
            $table->string('costRest', 25);
            $table->string('custNumber', 25);
            $table->string('currency', 10);
            $table->integer('userId');
            $table->integer('officeReId');
            $table->string('accept', 10);
            $table->integer('officeId');
            $table->integer('editToId')->default(0);
            $table->string('token')->nullable();
            $table->string('paidMethod');
            $table->boolean('paidInMainOffice')->default(false);
            // $table->foreign('customerId')->references('customerId')->on('customer')->nullOnDelete();
            // $table->foreign('officeId')->references('officeId')->on('office')->nullOnDelete();
            // $table->foreign('userId')->references('id')->on('users')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcels');
    }
};
