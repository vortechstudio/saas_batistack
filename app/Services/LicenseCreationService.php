<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\License;
use App\Enums\LicenseStatus;
use Carbon\Carbon;

class LicenseCreationService
{
    public function createLicenseFromInvoice(Invoice $invoice): License
    {
        // Récupérer les données de la commande depuis les métadonnées de la facture
        $orderData = json_decode((string) $invoice->metadata, true);

        // Calculer les dates
        $startsAt = now();
        $expiresAt = $orderData['billing_cycle'] === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        // Créer la licence
        $license = License::create([
            'customer_id' => $invoice->customer_id,
            'product_id' => $orderData['product_id'],
            'domain' => $orderData['domain'],
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

        // Créer la licence
        $license = License::create([
            'customer_id' => $licenseData['customer_id'],
            'product_id' => $licenseData['product_id'],
            'domain' => $licenseData['domain'],
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
