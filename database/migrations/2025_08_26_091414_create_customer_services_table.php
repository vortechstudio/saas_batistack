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
        Schema::create('customer_services', function (Blueprint $table) {
            $table->id();
            $table->string('service_code')->unique();
            $table->string('domain')->nullable();
            $table->date('creationDate');
            $table->date('expirationDate');
            $table->date('nextBillingDate');
            $table->string('status')->default('ok')->comment('expired┃ok┃pending┃unpaid');
            $table->string('stripe_subscription_id')->nullable();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_services');
    }
};
