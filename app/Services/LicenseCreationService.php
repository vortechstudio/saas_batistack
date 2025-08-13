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
        $orderData = json_decode($invoice->metadata, true);

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
}
