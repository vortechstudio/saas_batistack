<?php

use App\Enums\BillingCycle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_price', 8, 2);
            $table->string('billing_cycle')->default(BillingCycle::MONTHLY->value);
            $table->integer('max_users')->nullable();
            $table->integer('max_projects')->nullable();
            $table->integer('storage_limit')->nullable(); // en GB
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('stripe_price_id')->nullable();
            $table->timestamps();

            $table->index(['slug', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
