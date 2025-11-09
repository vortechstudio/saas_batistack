<?php

namespace Database\Seeders;

use App\Models\Product\Feature;
use App\Models\Product\Product;
use App\Services\Stripe\ProductService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productService = app(ProductService::class);
        $products = $productService->listWithPrices();

        // Créer les features à partir des modules gratuits uniquement
        $features = [];
        foreach ($products as $product)
        {
            // Ne créer des features que pour les modules gratuits (prix à 0)
            if (isset($product->metadata->category) &&
                $product->metadata->category === 'modules') {

                $slug = isset($product->metadata->slug) ? $product->metadata->slug : Str::slug($product->name);

                $feature = Feature::create([
                    'name' => $product->name,
                    'slug' => $slug,
                    'description' => $product->description,
                ]);
                $features[] = $feature;
            }
        }

        // Lier les features aux produits selon les licences
        $this->linkWithProducts($features);
    }

    private function linkWithProducts($features)
    {
        $starter = Product::where('slug', 'batistack-starter')->first();
        $pro = Product::where('slug', 'batistack-pro')->first();
        $ultimate = Product::where('slug', 'batistack-ultimate')->first();

        // Lier les features à chaque produit
        if ($starter) {
            $this->attachFeaturesToProduct($starter, $features, $this->getFeaturesByLicense('starter'));
        }

        if ($pro) {
            $this->attachFeaturesToProduct($pro, $features, $this->getFeaturesByLicense('pro'));
        }

        if ($ultimate) {
            $this->attachFeaturesToProduct($ultimate, $features, $this->getFeaturesByLicense('ultimate'));
        }
    }

    private function getFeaturesByLicense(string $license): array
    {
        return match ($license) {
            'starter' => [
                'Module Chantier',
                'Module Tiers',
                'Module Articles',
                'Module Commerces',
                'Module Facturation & Paiement',
                'Module Banques & Caisses',
                'Module Comptabilité'
            ],
            'pro' => [
                'Module Chantier',
                'Module Tiers',
                'Module Articles',
                'Module Commerces',
                'Module Facturation & Paiement',
                'Module Banques & Caisses',
                'Module Comptabilité',
                'Module GRH',
                'Module Paie',
                'Module Planning',
                'Module GED'
            ],
            'ultimate' => [
                'Module Tiers',
                'Module Articles',
                'Module Commerces',
                'Module Facturation & Paiement',
                'Module Banques & Caisses',
                'Module Comptabilité',
                'Module Chantier',
                'Module GRH',
                'Module Paie',
                'Module Planning',
                'Module GED',
                'Module GPAO',
                'Module 3D Vision'
            ],
            default => []
        };
    }

    private function attachFeaturesToProduct(Product $product, array $features, array $allowedFeatureNames): void
    {
        $featuresToAttach = [];

        foreach ($features as $feature) {
            if (in_array($feature->name, $allowedFeatureNames)) {
                $featuresToAttach[] = $feature->id;
            }
        }

        // Attacher les features au produit
        $product->features()->sync($featuresToAttach);
    }
}
