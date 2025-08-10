<?php

use App\Enums\BillingCycle;
use App\Enums\OptionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default(OptionType::FEATURE->value);
            $table->decimal('price', 8, 2);
            $table->string('billing_cycle')->default(BillingCycle::MONTHLY->value);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['key', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};
