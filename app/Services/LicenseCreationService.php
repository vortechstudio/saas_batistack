<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\License;
use App\Enums\LicenseStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseCreationService
{
    /**
     * Crée une licence avec les données fournies
     */
    public function createLicense(array $licenseData): License
    {
        // Calculer les dates
        $startsAt = now();
        $expiresAt = isset($licenseData['billing_cycle']) && $licenseData['billing_cycle'] === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        // Générer une clé de licence unique
        $licenseKey = $this->generateUniqueLicenseKey();

        // Créer la licence
        $license = License::create([
            'customer_id' => $licenseData['customer_id'],
            'product_id' => $licenseData['product_id'],
            'domain' => $licenseData['domain'],
            'license_key' => $licenseKey,
            'status' => LicenseStatus::ACTIVE,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'max_users' => $licenseData['max_users'] ?? 1, // Valeur par défaut de 1 au lieu de null
        ]);

        // Attacher les modules si fournis
        if (!empty($licenseData['modules'])) {
            foreach ($licenseData['modules'] as $moduleId) {
                $license->enableModule($moduleId, $expiresAt);
            }
        }

        // Attacher les options si fournies
        if (!empty($licenseData['options'])) {
            foreach ($licenseData['options'] as $optionId) {
                $license->options()->attach($optionId, [
                    'enabled' => true,
                    'expires_at' => $expiresAt,
                ]);
            }
        }

        return $license;
    }

    /**
     * Génère une clé de licence unique
     */
    private function generateUniqueLicenseKey(): string
    {
        do {
            $licenseKey = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (License::where('license_key', $licenseKey)->exists());

        return $licenseKey;
    }

    public function createLicenseFromInvoice(Invoice $invoice): License
    {
        // Récupérer les données de la commande depuis les métadonnées de la facture
        $orderData = $invoice->metadata ?? [];

        // Vérifier que les métadonnées nécessaires sont présentes
        if (empty($orderData['product_id']) || empty($orderData['domain'])) {
            throw new \Exception('Missing required metadata in invoice: product_id and domain are required');
        }

        // Calculer les dates
        $startsAt = now();
        $expiresAt = ($orderData['billing_cycle'] ?? 'monthly') === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        // Générer une clé de licence unique
        $licenseKey = $this->generateUniqueLicenseKey();

        // Créer la licence
        $license = License::create([
            'customer_id' => $invoice->customer_id,
            'product_id' => $orderData['product_id'],
            'domain' => $orderData['domain'],
            'license_key' => $licenseKey,
            'status' => LicenseStatus::ACTIVE,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'max_users' => $orderData['max_users'] ?? null,
        ]);

        // Attacher les modules
        if (!empty($orderData['modules'])) {
            foreach ($orderData['modules'] as $moduleId) {
                $license->enableModule($moduleId, $expiresAt);
            }
        }

        // Attacher les options
        if (!empty($orderData['options'])) {
            foreach ($orderData['options'] as $optionId) {
                $license->options()->attach($optionId, [
                    'enabled' => true,
                    'expires_at' => $expiresAt,
                ]);
            }
        }

        return $license;
    }

    /**
     * Crée une licence à partir d'une subscription Stripe
     */
    public function createLicenseFromSubscription(array $licenseData): License
    {
        // Calculer les dates
        $startsAt = now();
        $expiresAt = $licenseData['billing_cycle'] === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        // Générer une clé de licence unique
        $licenseKey = $this->generateUniqueLicenseKey();

        // Créer la licence
        $license = License::create([
            'customer_id' => $licenseData['customer_id'],
            'product_id' => $licenseData['product_id'],
            'domain' => $licenseData['domain'],
            'license_key' => $licenseKey,
            'status' => LicenseStatus::ACTIVE,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'stripe_subscription_id' => $licenseData['stripe_subscription_id'],
            'max_users' => $licenseData['max_users'] ?? null,
        ]);

        // Attacher les modules sélectionnés
        if (!empty($licenseData['selected_modules'])) {
            foreach ($licenseData['selected_modules'] as $moduleId) {
                $license->enableModule($moduleId, $expiresAt);
            }
        }

        // Attacher les options sélectionnées
        if (!empty($licenseData['selected_options'])) {
            foreach ($licenseData['selected_options'] as $optionId) {
                $license->options()->attach($optionId, [
                    'enabled' => true,
                    'expires_at' => $expiresAt,
                ]);
            }
        }

        return $license;
    }
}
