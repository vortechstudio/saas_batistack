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
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_method')->comment('card, bank_transfer, paypal, stripe, etc.');
            $table->string('status')->default('pending');
            $table->decimal('amount', 10, 2);
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->string('stripe_payment_method_id')->nullable();
            $table->string('stripe_customer_id')->nullable();

            // Informations de transaction
            $table->string('transaction_id')->nullable()->comment('ID de transaction externe');
            $table->string('reference')->nullable()->comment('Référence de paiement');

            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            // Remboursements
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->text('refund_reason')->nullable();

            // Métadonnées
            $table->json('gateway_response')->nullable()->comment('Réponse complète du gateway de paiement');
            $table->json('metadata')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('notes')->nullable();

            // Relations
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['status', 'created_at']);
            $table->index(['order_id', 'status']);
            $table->index('stripe_payment_intent_id');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
