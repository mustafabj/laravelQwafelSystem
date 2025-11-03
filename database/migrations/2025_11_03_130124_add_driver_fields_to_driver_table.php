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
        Schema::table('driver', function (Blueprint $table) {
            if (!Schema::hasColumn('driver', 'driverName')) {
                $table->string('driverName', 100)->after('driverId');
            }

            if (!Schema::hasColumn('driver', 'driverPhone')) {
                $table->string('driverPhone', 50)->nullable()->after('driverName');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver', function (Blueprint $table) {
            if (Schema::hasColumn('driver', 'driverName')) {
                $table->dropColumn('driverName');
            }

            if (Schema::hasColumn('driver', 'driverPhone')) {
                $table->dropColumn('driverPhone');
            }
        });
    }
};
