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
        Schema::create('customer_restricted_ips', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip_address');
            $table->string('authorize')->default('allow')->comment('allow or deny');
            $table->boolean('alert')->default(false);

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_restricted_ips');
    }
};
