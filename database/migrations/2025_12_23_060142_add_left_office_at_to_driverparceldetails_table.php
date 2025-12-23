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
        Schema::table('driverparceldetails', function (Blueprint $table) {
            if (! Schema::hasColumn('driverparceldetails', 'leftOfficeAt')) {
                $table->timestamp('leftOfficeAt')->nullable()->after('arrivedAt');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driverparceldetails', function (Blueprint $table) {
            if (Schema::hasColumn('driverparceldetails', 'leftOfficeAt')) {
                $table->dropColumn('leftOfficeAt');
            }
        });
    }
};
