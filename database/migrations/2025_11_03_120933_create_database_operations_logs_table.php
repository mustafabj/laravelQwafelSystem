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
        Schema::create('database_operations_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->timestamp('timestamp')->nullable()->useCurrent()->index('idx_timestamp');
            $table->enum('operation', ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP', 'ALTER', 'TRUNCATE'])->index('idx_operation');
            $table->string('table_name')->index('idx_table_name');
            $table->string('record_id')->nullable();
            $table->integer('affected_rows')->nullable();
            $table->string('query_hash', 64)->nullable();
            $table->decimal('execution_time', 10, 4)->nullable();
            $table->string('user_id')->nullable()->index('idx_user_id');
            $table->string('ip_address', 45)->nullable()->index('idx_ip_address');
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
        Schema::dropIfExists('database_operations_logs');
    }
};
