<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('title');
            $table->string('status')->default('investigating');
            $table->string('impact')->default('none'); // minor, major, critical, maintenance
            $table->timestamp('occurred_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // Table pivot Incidents <-> Composants
        Schema::create('incident_component', function (Blueprint $table) {
            $table->foreignId('incident_id')->constrained()->onDelete('cascade');
            $table->foreignId('system_component_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('incident_component');
    }
};
