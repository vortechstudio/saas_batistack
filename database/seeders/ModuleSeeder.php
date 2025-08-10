<?php

namespace Database\Seeders;

use App\Enums\ModuleCategory;
use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            // Modules Core (inclus dans tous les produits)
            [
                'key' => 'tiers',
                'name' => 'Tiers',
                'description' => 'Gestion des clients, fournisseurs et prospects',
                'category' => ModuleCategory::CORE,
                'base_price' => 0,
                'sort_order' => 1,
            ],
            [
                'key' => 'chantiers',
                'name' => 'Chantiers',
                'description' => 'Gestion des projets et chantiers',
                'category' => ModuleCategory::CORE,
                'base_price' => 0,
                'sort_order' => 2,
            ],
            [
                'key' => 'commerces',
                'name' => 'Commerces',
                'description' => 'Gestion commerciale et devis',
                'category' => ModuleCategory::CORE,
                'base_price' => 0,
                'sort_order' => 3,
            ],

            // Modules Advanced
            [
                'key' => 'rh',
                'name' => 'Ressources Humaines',
                'description' => 'Gestion du personnel et paie',
                'category' => ModuleCategory::ADVANCED,
                'base_price' => 29.99,
                'sort_order' => 4,
            ],
            [
                'key' => 'ged',
                'name' => 'GED',
                'description' => 'Gestion électronique des documents',
                'category' => ModuleCategory::ADVANCED,
                'base_price' => 19.99,
                'sort_order' => 5,
            ],
            [
                'key' => 'facturation',
                'name' => 'Facturation & Paiements',
                'description' => 'Gestion de la facturation et des paiements',
                'category' => ModuleCategory::ADVANCED,
                'base_price' => 39.99,
                'sort_order' => 6,
            ],
            [
                'key' => 'banques',
                'name' => 'Banques & Caisses',
                'description' => 'Gestion bancaire et de trésorerie',
                'category' => ModuleCategory::ADVANCED,
                'base_price' => 24.99,
                'sort_order' => 7,
            ],

            // Modules Premium
            [
                'key' => 'gpao',
                'name' => 'GPAO',
                'description' => 'Gestion de production assistée par ordinateur',
                'category' => ModuleCategory::PREMIUM,
                'base_price' => 59.99,
                'sort_order' => 8,
            ],
            [
                'key' => 'comptabilite',
                'name' => 'Comptabilité',
                'description' => 'Comptabilité en partie double',
                'category' => ModuleCategory::PREMIUM,
                'base_price' => 49.99,
                'sort_order' => 9,
            ],
            [
                'key' => 'plannings',
                'name' => 'Plannings',
                'description' => 'Gestion des plannings et ressources',
                'category' => ModuleCategory::PREMIUM,
                'base_price' => 34.99,
                'sort_order' => 10,
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::create($moduleData);
        }
    }
}
