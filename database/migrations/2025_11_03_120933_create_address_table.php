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
        if (Schema::hasTable('address')) {
            return;
        }

        Schema::create('address', function (Blueprint $table) {
            $table->integer('addressId', true);
            $table->integer('customerId')->index('fk_customerid_idx');
            $table->string('city');
            $table->string('area');
            $table->string('street');
            $table->string('buildingNumber');
            $table->string('info');
            $table->string('addedDay', 30);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address');
    }
};
