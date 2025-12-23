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
        if (Schema::hasTable('file_operations_logs')) {
            return;
        }

        Schema::create('file_operations_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->timestamp('timestamp')->nullable()->useCurrent()->index('idx_timestamp');
            $table->enum('operation', ['UPLOAD', 'DOWNLOAD', 'DELETE', 'MOVE', 'COPY', 'RENAME', 'MODIFY'])->index('idx_operation');
            $table->string('file_name', 500)->index('idx_file_name');
            $table->string('file_path', 1000)->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type', 100)->nullable()->index('idx_file_type');
            $table->string('mime_type', 100)->nullable();
            $table->string('original_name', 500)->nullable();
            $table->longText('validation_result')->nullable();
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
        Schema::dropIfExists('file_operations_logs');
    }
};
