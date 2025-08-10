<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->boolean('included')->default(true);
            $table->decimal('price_override', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_modules');
    }
};
