<?php

namespace App\Services\Stripe;

use App\Models\Customer\Customer;
use App\Models\Customer\CustomerPaymentMethod;

class PaymentMethodService extends StripeService
{
    /**
     * Lister tous les moyens de paiement d'un client
     */
    public function listPaymentMethods(Customer $customer)
    {
        try {
            return collect($this->client->customers->allPaymentMethods($customer->stripe_customer_id));
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Récupérer un moyen de paiement spécifique
     */
    public function getPaymentMethod(string $paymentMethodId)
    {
        try {
            return $this->client->paymentMethods->retrieve($paymentMethodId);
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Attacher un moyen de paiement à un client
     */
    public function attachPaymentMethod(string $paymentMethodId, Customer $customer)
    {
        try {
            $paymentMethod = $this->client->paymentMethods->attach($paymentMethodId, [
                'customer' => $customer->stripe_customer_id,
            ]);

            // Sauvegarder en base de données locale
            CustomerPaymentMethod::create([
                'stripe_payment_method_id' => $paymentMethodId,
                'customer_id' => $customer->id,
                'is_active' => true,
                'is_default' => false,
            ]);

            return $paymentMethod;
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Détacher un moyen de paiement d'un client
     */
    public function detachPaymentMethod(string $paymentMethodId)
    {
        try {
            $result = $this->client->paymentMethods->detach($paymentMethodId);

            // Supprimer de la base de données locale
            CustomerPaymentMethod::where('stripe_payment_method_id', $paymentMethodId)->delete();

            return $result;
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Mettre à jour un moyen de paiement
     */
    public function updatePaymentMethod(string $paymentMethodId, array $data)
    {
        try {
            return $this->client->paymentMethods->update($paymentMethodId, $data);
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Définir un moyen de paiement comme par défaut
     */
    public function setDefaultPaymentMethod(Customer $customer, string $paymentMethodId)
    {
        try {
            // Mettre à jour le client Stripe
            $this->client->customers->update($customer->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            // Mettre à jour la base de données locale
            CustomerPaymentMethod::where('customer_id', $customer->id)
                ->update(['is_default' => false]);

            CustomerPaymentMethod::where('stripe_payment_method_id', $paymentMethodId)
                ->update(['is_default' => true]);

            return true;
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Créer une session de configuration pour ajouter un moyen de paiement
     */
    public function createSetupSession(Customer $customer, string $returnUrl)
    {
        try {
            return $this->client->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'mode' => 'setup',
                'customer' => $customer->stripe_customer_id,
                'success_url' => $returnUrl . '?setup=success',
                'cancel_url' => $returnUrl . '?setup=cancel',
            ]);
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Récupérer un client Stripe
     */
    public function getStripeCustomer(string $stripeCustomerId)
    {
        try {
            return $this->client->customers->retrieve($stripeCustomerId);
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }
}