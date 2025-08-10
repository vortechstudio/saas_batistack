<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Enums\OptionType;
use App\Models\Option;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            // Options de fonctionnalités
            [
                'key' => 'backup_retention',
                'name' => 'Backup & Rétention',
                'description' => 'Sauvegarde automatique et rétention des données sur 5 ans',
                'type' => OptionType::FEATURE,
                'price' => 14.99,
                'billing_cycle' => BillingCycle::MONTHLY,
            ],
            [
                'key' => 'bank_aggregation',
                'name' => 'Agrégation bancaire',
                'description' => 'Connexion automatique aux comptes bancaires',
                'type' => OptionType::FEATURE,
                'price' => 19.99,
                'billing_cycle' => BillingCycle::MONTHLY,
            ],
            [
                'key' => 'ocr_invoices',
                'name' => 'OCR Factures',
                'description' => 'Reconnaissance optique des factures et documents',
                'type' => OptionType::FEATURE,
                'price' => 24.99,
                'billing_cycle' => BillingCycle::MONTHLY,
            ],
            [
                'key' => 'ocr_expenses',
                'name' => 'OCR Notes de frais',
                'description' => 'Reconnaissance optique des notes de frais',
                'type' => OptionType::FEATURE,
                'price' => 19.99,
                'billing_cycle' => BillingCycle::MONTHLY,
            ],
            [
                'key' => 'api_access',
                'name' => 'Accès API',
                'description' => 'Accès complet à l\'API REST pour intégrations',
                'type' => OptionType::FEATURE,
                'price' => 29.99,
                'billing_cycle' => BillingCycle::MONTHLY,
            ],

            // Options de support
            [
                'key' => 'premium_support',
                'name' => 'Support Premium',
                'description' => 'Support prioritaire 24/7 avec temps de réponse garanti',
                'type' => OptionType::SUPPORT,
                'price' => 49.99,
                'billing_cycle' => BillingCycle::MONTHLY,
            ],
            [
                'key' => 'extended_support',
                'name' => 'Support Étendu',
                'description' => 'Support technique étendu avec formation personnalisée',
                'type' => OptionType::SUPPORT,
                'price' => 99.99,
                'billing_cycle' => BillingCycle::MONTHLY,
            ],

            // Options de stockage
            [
                'key' => 'extra_storage_50gb',
                'name' => 'Stockage +50GB',
                'description' => 'Espace de stockage supplémentaire de 50GB',
                'type' => OptionType::STORAGE,
                'price' => 9.99,
                'billing_cycle' => BillingCycle::MONTHLY,
            ],
            [
                'key' => 'extra_storage_100gb',
                'name' => 'Stockage +100GB',
                'description' => 'Espace de stockage supplémentaire de 100GB',
                'type' => OptionType::STORAGE,
                'price' => 17.99,
                'billing_cycle' => BillingCycle::MONTHLY,
            ],
            [
                'key' => 'extra_storage_500gb',
                'name' => 'Stockage +500GB',
                'description' => 'Espace de stockage supplémentaire de 500GB',
                'type' => OptionType::STORAGE,
                'price' => 79.99,
                'billing_cycle' => BillingCycle::MONTHLY,
            ],
        ];

        foreach ($options as $optionData) {
            Option::create($optionData);
        }
    }
}
