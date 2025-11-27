<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer', function (Blueprint $table) {

            // Phone indexes
            $table->index('phone1', 'idx_customer_phone1');
            $table->index('phone2', 'idx_customer_phone2');
            $table->index('phone3', 'idx_customer_phone3');
            $table->index('phone4', 'idx_customer_phone4');

            // Name indexes
            $table->index('FName', 'idx_customer_fname');
            $table->index('LName', 'idx_customer_lname');

        });
    }

    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {

            // Drop indexes
            $table->dropIndex('idx_customer_phone1');
            $table->dropIndex('idx_customer_phone2');
            $table->dropIndex('idx_customer_phone3');
            $table->dropIndex('idx_customer_phone4');

            $table->dropIndex('idx_customer_fname');
            $table->dropIndex('idx_customer_lname');
        });
    }
};
