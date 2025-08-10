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

class DemoSeeder extends Seeder
{
    /**
     * Seeder spécialement conçu pour les démonstrations
     */
    public function run(): void
    {
        // Créer des entreprises réalistes du BTP
        $companies = [
            [
                'company_name' => 'Constructions Dubois & Fils',
                'contact_name' => 'Pierre Dubois',
                'email' => 'p.dubois@constructions-dubois.fr',
                'phone' => '01 42 36 78 90',
                'city' => 'Marseille',
                'activity' => 'Gros œuvre',
            ],
            [
                'company_name' => 'Électricité Moderne SARL',
                'contact_name' => 'Sophie Leclerc',
                'email' => 's.leclerc@elec-moderne.fr',
                'phone' => '04 78 45 23 67',
                'city' => 'Lyon',
                'activity' => 'Électricité',
            ],
            [
                'company_name' => 'Plomberie & Chauffage Martin',
                'contact_name' => 'Jean-Claude Martin',
                'email' => 'jc.martin@plomberie-martin.fr',
                'phone' => '05 56 78 90 12',
                'city' => 'Bordeaux',
                'activity' => 'Plomberie',
            ],
            [
                'company_name' => 'Menuiserie Artisanale Rousseau',
                'contact_name' => 'Amélie Rousseau',
                'email' => 'a.rousseau@menuiserie-rousseau.fr',
                'phone' => '02 98 34 56 78',
                'city' => 'Brest',
                'activity' => 'Menuiserie',
            ],
        ];

        $products = Product::where('billing_cycle', 'monthly')->get();

        foreach ($companies as $index => $companyData) {
            // Créer l'utilisateur
            $user = User::factory()->create([
                'name' => $companyData['contact_name'],
                'email' => $companyData['email'],
            ]);

            // Créer le client
            $customer = Customer::factory()->create([
                'company_name' => $companyData['company_name'],
                'contact_name' => $companyData['contact_name'],
                'email' => $companyData['email'],
                'phone' => $companyData['phone'],
                'city' => $companyData['city'],
                'status' => CustomerStatus::ACTIVE,
                'user_id' => $user->id,
            ]);

            // Assigner un produit selon la taille de l'entreprise
            $product = match ($index) {
                0 => $products->where('slug', 'batistack-enterprise')->first(),
                1, 2 => $products->where('slug', 'batistack-professional')->first(),
                default => $products->where('slug', 'batistack-starter')->first(),
            };

            // Créer la licence
            $license = License::factory()->create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'status' => LicenseStatus::ACTIVE,
                'starts_at' => now()->subMonths(rand(1, 12)),
                'expires_at' => now()->addMonths(rand(6, 18)),
                'max_users' => $product->max_users ?? rand(5, 20),
                'current_users' => rand(1, min(10, $product->max_users ?? 10)),
                'last_used_at' => now()->subHours(rand(1, 48)),
            ]);

            // Activer les modules inclus
            $includedModules = $product->modules()->wherePivot('included', true)->get();
            foreach ($includedModules as $module) {
                $license->modules()->attach($module->id, [
                    'enabled' => true,
                    'expires_at' => $license->expires_at,
                ]);
            }

            // Activer quelques modules optionnels selon le produit
            if ($product->slug !== 'batistack-starter') {
                $optionalModules = Module::whereNotIn('id', $includedModules->pluck('id'))
                    ->inRandomOrder()
                    ->take(rand(1, 3))
                    ->get();

                foreach ($optionalModules as $module) {
                    $license->modules()->attach($module->id, [
                        'enabled' => true,
                        'expires_at' => $license->expires_at,
                    ]);
                }
            }

            // Activer quelques options
            $availableOptions = $product->options()->inRandomOrder()->take(rand(2, 4))->get();
            foreach ($availableOptions as $option) {
                $license->options()->attach($option->id, [
                    'enabled' => true,
                    'expires_at' => $license->expires_at,
                ]);
            }
        }

        // Créer quelques licences d'essai
        $this->createTrialLicenses();

        // Créer quelques licences expirées pour les tests
        $this->createExpiredLicenses();
    }

    private function createTrialLicenses(): void
    {
        $starterProduct = Product::where('slug', 'batistack-starter')->first();

        for ($i = 0; $i < 3; $i++) {
            $user = User::factory()->create();
            $customer = Customer::factory()->create([
                'user_id' => $user->id,
                'status' => CustomerStatus::ACTIVE,
            ]);

            $license = License::factory()->create([
                'customer_id' => $customer->id,
                'product_id' => $starterProduct->id,
                'status' => LicenseStatus::ACTIVE,
                'starts_at' => now()->subDays(rand(1, 20)),
                'expires_at' => now()->addDays(rand(10, 30)),
                'max_users' => 2,
                'current_users' => rand(1, 2),
                'last_used_at' => now()->subHours(rand(1, 24)),
            ]);

            // Activer seulement les modules core pour l'essai
            $coreModules = Module::where('category', 'core')->get();
            foreach ($coreModules as $module) {
                $license->modules()->attach($module->id, [
                    'enabled' => true,
                    'expires_at' => $license->expires_at,
                ]);
            }
        }
    }

    private function createExpiredLicenses(): void
    {
        for ($i = 0; $i < 2; $i++) {
            $user = User::factory()->create();
            $customer = Customer::factory()->create([
                'user_id' => $user->id,
                'status' => CustomerStatus::INACTIVE,
            ]);

            License::factory()->create([
                'customer_id' => $customer->id,
                'product_id' => Product::inRandomOrder()->first()->id,
                'status' => LicenseStatus::EXPIRED,
                'starts_at' => now()->subYear(),
                'expires_at' => now()->subMonths(rand(1, 6)),
                'max_users' => rand(3, 10),
                'current_users' => 0,
                'last_used_at' => now()->subMonths(rand(2, 8)),
            ]);
        }
    }
}