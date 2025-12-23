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
        if (Schema::hasTable('parcelsdetails')) {
            return;
        }

        Schema::create('parcelsdetails', function (Blueprint $table) {
            $table->integer('detailId', true);
            $table->integer('detailQun');
            $table->string('detailInfo');
            $table->integer('parcelId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcelsdetails');
    }
};
