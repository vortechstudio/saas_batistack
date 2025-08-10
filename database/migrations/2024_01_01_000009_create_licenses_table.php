<?php

use App\Enums\LicenseStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('license_key')->unique();
            $table->string('status')->default(LicenseStatus::ACTIVE->value);
            $table->datetime('starts_at');
            $table->datetime('expires_at')->nullable();
            $table->integer('max_users')->default(1);
            $table->integer('current_users')->default(0);
            $table->datetime('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['license_key', 'status']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
