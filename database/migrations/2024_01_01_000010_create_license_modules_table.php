<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->boolean('enabled')->default(true);
            $table->datetime('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['license_id', 'module_id']);
            $table->index(['license_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_modules');
    }
};
