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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code_client')->unique();
            $table->string('type_compte')->default('particulier');
            $table->string('entreprise')->nullable();
            $table->string('adresse');
            $table->string('code_postal');
            $table->string('ville');
            $table->string('pays');
            $table->string('tel')->nullable();
            $table->string('portable')->nullable();
            $table->string('support_type')->default('standard')->comment('Type de support (standard, Premium, Business, Entreprise)');

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
