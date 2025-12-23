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
        if (Schema::hasTable('fleetdet')) {
            return;
        }

        Schema::create('fleetdet', function (Blueprint $table) {
            $table->integer('detId', true);
            $table->string('detail');
            $table->integer('fleetId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleetdet');
    }
};
