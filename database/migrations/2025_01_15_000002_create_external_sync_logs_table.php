<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('system_name'); // nom du système externe
            $table->string('operation'); // sync, export, import
            $table->string('entity_type'); // customers, licenses, products
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('status'); // pending, running, success, failed
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['system_name', 'status']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_sync_logs');
    }
};