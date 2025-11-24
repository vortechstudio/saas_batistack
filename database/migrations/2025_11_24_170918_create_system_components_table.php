<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Les composants du système (API, Web, Database, etc.)
        Schema::create('system_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('group')->nullable(); // ex: Infrastructure, Application...
            $table->string('status')->default('operational');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_components');
    }
};
