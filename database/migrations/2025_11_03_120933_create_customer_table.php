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
        Schema::create('customer', function (Blueprint $table) {
            $table->integer('customerId', true);
            $table->string('FName');
            $table->string('LName');
            $table->string('customerPassport');
            $table->string('customerState')->nullable();
            $table->string('phone1', 25);
            $table->string('phone2', 25);
            $table->string('phone3', 25);
            $table->string('phone4', 25);
            $table->timestamp('addedDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
