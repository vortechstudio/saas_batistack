<?php

namespace App\Services\Stripe;

use App\Models\Customer\Customer;
use Illuminate\Support\Str;
use League\ISO3166\ISO3166;
use Log;
use OutOfBoundsException;

class CustomerService extends StripeService
{
    public function create(Customer $customer): void
    {
        if ($customer->stripe_customer_id) {
            Log::info("Client Stripe déjà existant pour le customer {$customer->id}");
            return;
        }

        $customer->loadMissing('user');

        try {
            $countryCode = $this->resolveIsoCountryCode($customer->pays);

            $stripeCustomer = $this->client->customers->create([
                'name' => $customer->entreprise,
                // Utilisation du null coalescing operator pour la sécurité
                'email' => $customer->user?->email,
                'phone' => $customer->tel ?? $customer->portable,
                'metadata' => [
                    'customer_id' => $customer->id,
                    'env' => app()->environment(), // Utile pour le debug sur Stripe
                ],
                'address' => [
                    'line1' => $customer->adresse,
                    'postal_code' => $customer->code_postal,
                    'city' => $customer->ville,
                    // TODO: S'assurer que 'pays' est bien un code ISO (FR, US, etc.)
                    // Si 'pays' est un nom complet, cette ligne reste un point de défaillance.
                    'country' => $countryCode,
                ]
            ]);

            // 3. Update ciblé pour éviter les race conditions
            $customer->updateQuietly([
                'stripe_customer_id' => $stripeCustomer->id
            ]);
        }catch(\Exception $e) {
            // 4. Niveau de log approprié + Context
            Log::error("Erreur lors de la création du client Stripe : " . $e->getMessage(), [
                'customer_id' => $customer->id,
                'exception' => $e
            ]);
            report($e);
        }
    }

    public function listPaymentMethods(Customer $customer)
    {
        try {
            return collect($this->client->customers->allPaymentMethods($customer->stripe_customer_id));
        }catch(\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    public function listInvoices(Customer $customer)
    {
        try {
            return collect($this->client->invoices->all([
                'customer' => $customer->stripe_customer_id,
            ]));
        }catch(\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    public function getInvoice(string $invoiceId)
    {
        try {
            return $this->client->invoices->retrieve($invoiceId);
        }catch(\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    public function payInvoice(string $invoiceId)
    {
        try {
            return $this->client->invoices->pay($invoiceId);
        }catch(\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Tente de résoudre un code pays valide (ex: 'FR') à partir d'une chaine.
     */
    private function resolveIsoCountryCode(?string $inputCountry): string
    {
        $iso = new ISO3166();
        if (empty($inputCountry)) {
            return 'FR'; // Fallback par défaut si le champ est vide
        }

        try {
            // Tentative 1 : Recherche par nom (ex: "France", "United States")
            // Attention : La librairie attend des noms en Anglais par défaut.
            $data = $iso->name($inputCountry);
            return $data['alpha2'];
        } catch (OutOfBoundsException $e) {
            // Tentative 2 : L'input est peut-être déjà un code (ex: "FR", "BE")
            try {
                $data = $iso->alpha2($inputCountry);
                return $data['alpha2'];
            } catch (OutOfBoundsException $e2) {
                // Échec total
                Log::warning("CustomerService: Impossible de résoudre le code pays pour '{$inputCountry}'. Fallback sur 'FR'.");
                return 'FR'; // Valeur de repli pour ne pas faire planter l'API Stripe
            }
        }
    }
}
