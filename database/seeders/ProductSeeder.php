<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Models\Module;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Stripe\StripeClient;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les modules par catégorie
        $coreModules = Module::where('category', 'core')->get();
        $advancedModules = Module::where('category', 'advanced')->get();
        $premiumModules = Module::where('category', 'premium')->get();

        // Récupérer toutes les options
        $allOptions = Option::all();


        // Produit Starter
        $starter = Product::create([
            'name' => 'Batistack Starter',
            'slug' => 'batistack-starter',
            'description' => 'Solution de base pour les petites entreprises du BTP. Idéale pour débuter avec les fonctionnalités essentielles.',
            'base_price' => 49.99,
            'billing_cycle' => BillingCycle::MONTHLY,
            'max_users' => 3,
            'max_projects' => 10,
            'storage_limit' => 5,
            'is_active' => true,
            'is_featured' => false,
            'stripe_price_id' => 'price_1RvheNCEoUhpOO020AQyGDNz',
            'stripe_price_id_monthly' => 'price_1RvheNCEoUhpOO020AQyGDNz',
            'stripe_price_id_yearly' => 'price_1RvheNCEoUhpOO027lChAu9r',
        ]);

        // Associer les modules core (inclus) au Starter
        foreach ($coreModules as $module) {
            $starter->modules()->attach($module->id, ['included' => true]);
        }

        // Associer quelques options au Starter
        $starter->options()->attach($allOptions->whereIn('key', [
            'backup_retention', 'extra_storage_50gb', 'extra_storage_100gb'
        ])->pluck('id'));

        // Produit Professional
        $professional = Product::create([
            'name' => 'Batistack Professional',
            'slug' => 'batistack-professional',
            'description' => 'Solution complète pour les entreprises en croissance. Inclut les modules avancés pour une gestion optimisée.',
            'base_price' => 99.99,
            'billing_cycle' => BillingCycle::MONTHLY,
            'max_users' => 10,
            'max_projects' => 50,
            'storage_limit' => 25,
            'is_active' => true,
            'is_featured' => true,
            'stripe_price_id' => 'price_1RvhhdCEoUhpOO02DCazEz58',
            'stripe_price_id_monthly' => 'price_1RvhhdCEoUhpOO02DCazEz58',
            'stripe_price_id_yearly' => 'price_1RvhiBCEoUhpOO02zqacWwiR',
        ]);

        // Associer les modules core (inclus) et advanced (inclus) au Professional
        foreach ($coreModules as $module) {
            $professional->modules()->attach($module->id, ['included' => true]);
        }
        foreach ($advancedModules as $module) {
            $professional->modules()->attach($module->id, ['included' => true]);
        }

        // Associer toutes les options au Professional
        $professional->options()->attach($allOptions->pluck('id'));

        // Produit Enterprise
        $enterprise = Product::create([
            'name' => 'Batistack Enterprise',
            'slug' => 'batistack-enterprise',
            'description' => 'Solution avancée pour les grandes entreprises. Accès complet à tous les modules et fonctionnalités premium.',
            'base_price' => 199.99,
            'billing_cycle' => BillingCycle::MONTHLY,
            'max_users' => null, // Illimité
            'max_projects' => null, // Illimité
            'storage_limit' => 100,
            'is_active' => true,
            'is_featured' => false,
            'stripe_price_id' => 'price_1RvhjECEoUhpOO02mcKh6dZp',
            'stripe_price_id_monthly' => 'price_1RvhjECEoUhpOO02mcKh6dZp',
            'stripe_price_id_yearly' => 'price_1RvhjVCEoUhpOO02hbpUvmML',
        ]);

        // Associer tous les modules (inclus) à Enterprise
        foreach ($coreModules as $module) {
            $enterprise->modules()->attach($module->id, ['included' => true]);
        }
        foreach ($advancedModules as $module) {
            $enterprise->modules()->attach($module->id, ['included' => true]);
        }
        foreach ($premiumModules as $module) {
            $enterprise->modules()->attach($module->id, ['included' => true]);
        }

        // Associer toutes les options à Enterprise
        $enterprise->options()->attach($allOptions->pluck('id'));

        // Créer les versions annuelles
        $this->createYearlyVersions();
    }

    private function createYearlyVersions(): void
    {
        $monthlyProducts = Product::where('billing_cycle', BillingCycle::MONTHLY)->get();

        foreach ($monthlyProducts as $monthlyProduct) {
            $yearlyProduct = Product::create([
                'name' => $monthlyProduct->name . ' (Annuel)',
                'slug' => $monthlyProduct->slug . '-yearly',
                'description' => $monthlyProduct->description . ' Facturation annuelle avec 2 mois offerts.',
                'base_price' => $monthlyProduct->base_price * 10, // 10 mois au lieu de 12
                'billing_cycle' => BillingCycle::YEARLY,
                'max_users' => $monthlyProduct->max_users,
                'max_projects' => $monthlyProduct->max_projects,
                'storage_limit' => $monthlyProduct->storage_limit,
                'is_active' => true,
                'is_featured' => $monthlyProduct->is_featured,
                'stripe_price_id' => $monthlyProduct->stripe_price_id,
                'stripe_price_id_monthly' => $monthlyProduct->stripe_price_id_monthly,
                'stripe_price_id_yearly' => $monthlyProduct->stripe_price_id_yearly,
            ]);

            // Copier les relations modules
            foreach ($monthlyProduct->modules as $module) {
                $yearlyProduct->modules()->attach($module->id, [
                    'included' => $module->pivot->included,
                    'price_override' => $module->pivot->price_override,
                ]);
            }

            // Copier les relations options
            $yearlyProduct->options()->attach($monthlyProduct->options->pluck('id'));
        }
    }
}
