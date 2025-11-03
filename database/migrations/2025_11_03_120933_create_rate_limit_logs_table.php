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
        Schema::create('rate_limit_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->timestamp('timestamp')->nullable()->useCurrent()->index('idx_timestamp');
            $table->string('action', 100)->index('idx_action');
            $table->string('ip_address', 45)->index('idx_ip_address');
            $table->string('user_id')->nullable()->index('idx_user_id');
            $table->integer('attempt_count')->nullable()->default(1);
            $table->boolean('limit_exceeded')->nullable()->default(false)->index('idx_limit_exceeded');
            $table->boolean('blocked')->nullable()->default(false);
            $table->text('user_agent')->nullable();
            $table->string('request_uri', 500)->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_limit_logs');
    }
};
