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
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'position_id')) {
                $columnsToDrop[] = 'position_id';
            }
            if (Schema::hasColumn('users', 'remember_token_expiry')) {
                $columnsToDrop[] = 'remember_token_expiry';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            if (! Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }

            $table->string('email', 255)->nullable()->change();
            $table->string('password', 255)->nullable(false)->change();

            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();

            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('email');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('position_id')->nullable();
            $table->dateTime('remember_token_expiry')->nullable();

            $table->dropColumn(['email_verified_at', 'phone']);
        });
    }
};
