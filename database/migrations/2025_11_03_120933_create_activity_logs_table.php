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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->timestamp('timestamp')->nullable()->useCurrent()->index('idx_timestamp');
            $table->string('action')->index('idx_action');
            $table->enum('action_type', ['CREATE', 'READ', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'UPLOAD', 'DOWNLOAD', 'EXPORT', 'IMPORT', 'OTHER'])->nullable()->default('OTHER')->index('idx_action_type');
            $table->string('resource_type', 100)->nullable()->index('idx_resource_type');
            $table->string('resource_id')->nullable();
            $table->text('description')->nullable();
            $table->longText('details')->nullable();
            $table->string('user_id')->nullable()->index('idx_user_id');
            $table->string('username')->nullable();
            $table->string('ip_address', 45)->nullable()->index('idx_ip_address');
            $table->text('user_agent')->nullable();
            $table->string('request_uri', 500)->nullable();
            $table->string('request_method', 10)->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
