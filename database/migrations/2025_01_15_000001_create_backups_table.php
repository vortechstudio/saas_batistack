<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // full, incremental, differential
            $table->string('status'); // pending, running, completed, failed
            $table->string('storage_driver'); // local, s3, ftp, etc.
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable(); // en bytes
            $table->json('metadata')->nullable(); // informations supplémentaires
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};