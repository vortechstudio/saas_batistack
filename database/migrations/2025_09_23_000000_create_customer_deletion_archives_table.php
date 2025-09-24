<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_deletion_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('customer_code');
            $table->timestamp('deletion_date');
            $table->text('deletion_reason')->nullable();
            $table->json('financial_summary');
            $table->timestamp('legal_retention_period');
            $table->timestamps();

            $table->index(['customer_id', 'deletion_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_deletion_archives');
    }
};
