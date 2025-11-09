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
        Schema::create('customer_service_steps', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('license, modules, options');
            $table->string('step');
            $table->boolean('done')->default(false);
            $table->text('comment')->nullable();
            $table->foreignId('customer_service_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_service_steps');
    }
};
