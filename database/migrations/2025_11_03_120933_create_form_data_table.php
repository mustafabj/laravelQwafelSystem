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
        Schema::create('form_data', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name');
            $table->string('phone', 20);
            $table->date('date');
            $table->integer('adults');
            $table->integer('children');
            $table->string('origin');
            $table->string('destination');
            $table->integer('see');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_data');
    }
};
