<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Enums\LicenseStatus;
use App\Models\Customer;
use App\Models\License;
use App\Models\Module;
use App\Models\Option;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des utilisateurs de test (éviter les doublons)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@batistack.com'],
            [
                'name' => 'Admin Batistack',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $testUser = User::firstOrCreate(
            ['email' => 'jean.dupont@test.com'],
            [
                'name' => 'Jean Dupont',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $demoUser = User::firstOrCreate(
            ['email' => 'marie.martin@demo.com'],
            [
                'name' => 'Marie Martin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Créer des clients de test
        $activeCustomer = Customer::factory()->create([
            'company_name' => 'Entreprise Dupont BTP',
            'contact_name' => 'Jean Dupont',
            'email' => 'jean.dupont@test.com',
            'phone' => '01 23 45 67 89',
            'address' => '123 Rue de la Construction',
            'city' => 'Paris',
            'postal_code' => '75001',
            'country' => 'FR',
            'siret' => '12345678901234',
            'vat_number' => 'FR12345678901',
            'status' => CustomerStatus::ACTIVE,
            'user_id' => $testUser->id,
        ]);

        $trialCustomer = Customer::factory()->create([
            'company_name' => 'Martin Construction',
            'contact_name' => 'Marie Martin',
            'email' => 'marie.martin@demo.com',
            'phone' => '01 98 76 54 32',
            'address' => '456 Avenue du Bâtiment',
            'city' => 'Lyon',
            'postal_code' => '69001',
            'country' => 'FR',
            'siret' => '98765432109876',
            'vat_number' => 'FR98765432109',
            'status' => CustomerStatus::ACTIVE,
            'user_id' => $demoUser->id,
        ]);

        // Récupérer les produits existants (créés par ProductSeeder)
        $starterProduct = Product::where('slug', 'batistack-starter')->first();
        $professionalProduct = Product::where('slug', 'batistack-professional')->first();
        $enterpriseProduct = Product::where('slug', 'batistack-enterprise')->first();

        // Vérifier que les produits existent
        if (!$starterProduct || !$professionalProduct || !$enterpriseProduct) {
            throw new \Exception('Les produits de base doivent être créés avant d\'exécuter TestDataSeeder. Assurez-vous que ProductSeeder s\'exécute en premier.');
        }

        // Créer des licences de test
        $activeLicense = License::factory()->create([
            'customer_id' => $activeCustomer->id,
            'product_id' => $professionalProduct->id,
            'status' => LicenseStatus::ACTIVE,
            'starts_at' => now()->subMonths(3),
            'expires_at' => now()->addMonths(9),
            'max_users' => 10,
            'current_users' => 5,
            'last_used_at' => now()->subHours(2),
        ]);

        $trialLicense = License::factory()->create([
            'customer_id' => $trialCustomer->id,
            'product_id' => $starterProduct->id,
            'status' => LicenseStatus::ACTIVE,
            'starts_at' => now()->subDays(15),
            'expires_at' => now()->addDays(15), // Essai de 30 jours
            'max_users' => 3,
            'current_users' => 2,
            'last_used_at' => now()->subMinutes(30),
        ]);

        // Licence expirée pour les tests
        $expiredLicense = License::factory()->create([
            'customer_id' => $activeCustomer->id,
            'product_id' => $starterProduct->id,
            'status' => LicenseStatus::EXPIRED,
            'starts_at' => now()->subYear(),
            'expires_at' => now()->subMonths(2),
            'max_users' => 3,
            'current_users' => 0,
            'last_used_at' => now()->subMonths(3),
        ]);

        // Associer des modules aux licences
        $this->attachModulesToLicense($activeLicense);
        $this->attachModulesToLicense($trialLicense);

        // Associer des options aux licences
        $this->attachOptionsToLicense($activeLicense);
        $this->attachOptionsToLicense($trialLicense);

        // Créer des données supplémentaires pour les tests
        $this->createAdditionalTestData();
    }

    private function attachModulesToLicense(License $license): void
    {
        $product = $license->product;
        $includedModules = $product->modules()->wherePivot('included', true)->get();

        foreach ($includedModules as $module) {
            $license->modules()->attach($module->id, [
                'enabled' => true,
                'expires_at' => $license->expires_at,
            ]);
        }

        // Ajouter quelques modules optionnels pour la licence Professional
        if ($product->slug === 'batistack-professional') {
            $optionalModules = Module::where('category', 'premium')->take(2)->get();
            foreach ($optionalModules as $module) {
                $license->modules()->attach($module->id, [
                    'enabled' => true,
                    'expires_at' => $license->expires_at,
                ]);
            }
        }
    }

    private function attachOptionsToLicense(License $license): void
    {
        $product = $license->product;
        $availableOptions = $product->options;

        // Activer quelques options aléatoirement
        $enabledOptions = $availableOptions->random(min(3, $availableOptions->count()));

        foreach ($enabledOptions as $option) {
            $license->options()->attach($option->id, [
                'enabled' => true,
                'expires_at' => $license->expires_at,
            ]);
        }
    }

    private function createAdditionalTestData(): void
    {
        // Récupérer les produits existants pour les licences supplémentaires
        $products = Product::all();

        if ($products->isEmpty()) {
            return; // Pas de produits disponibles
        }

        // Créer quelques clients supplémentaires avec différents statuts
        $additionalCustomers = Customer::factory()->count(5)->create();
        Customer::factory()->suspended()->count(2)->create();
        Customer::factory()->inactive()->count(1)->create();

        // Créer quelques licences supplémentaires avec des produits existants
        foreach ($additionalCustomers as $customer) {
            $randomProduct = $products->random();
            License::factory()->create([
                'customer_id' => $customer->id,
                'product_id' => $randomProduct->id,
            ]);
        }

        // Créer des licences avec différents statuts
        License::factory()->expired()->count(3)->create([
            'product_id' => $products->random()->id,
        ]);

        License::factory()->suspended()->count(2)->create([
            'product_id' => $products->random()->id,
        ]);

        // Créer des utilisateurs supplémentaires
        User::factory()->count(10)->create();
    }
}
