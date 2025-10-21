<?php

namespace App\Console\Commands\Core;

use App\Enum\Product\ProductCategoryEnum;
use App\Enum\Product\ProductPriceFrequencyEnum;
use App\Models\Product\Product;
use App\Models\Product\ProductPrice;
use App\Services\Stripe\ProductService;
use App\Services\Stripe\StripeService;
use Illuminate\Console\Command;

class UpdateProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mise à jour des produits en relation avec stripe';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('Mise à jour des produits en relation avec stripe');
        $products = app(ProductService::class)->listWithPrices();
        $this->info('Nombre de produits en relation avec stripe : ' . count($products));
        foreach ($products as $product) {
            $this->info('Mise à jour du produit : ' . $product->name);

            $category = match($product->metadata->category) {
                'modules' => ProductCategoryEnum::MODULE,
                'options' => ProductCategoryEnum::OPTION,
                'license' => ProductCategoryEnum::LICENSE,
                'support' => ProductCategoryEnum::SUPPORT
            };

            $pr = Product::updateOrCreate([
                'stripe_product_id' => $product->id,
            ], [
                'name' => $product->name,
                'slug' => $product->metadata->slug,
                'category' => $category,
                'description' => $product->description,
                'stripe_product_id' => $product->id,
            ]);

            foreach ($product->prices as $price) {
                $this->info('Mise à jour du prix : ' . $price->id);

                // Mapper la fréquence Stripe vers l'enum
                if(isset($price->recurring->interval)) {
                    $frequency = match($price->recurring->interval) {
                        'month' => ProductPriceFrequencyEnum::MONTHLY,
                        'year' => ProductPriceFrequencyEnum::ANNUAL,
                        default => ProductPriceFrequencyEnum::UNIQUE,
                    };
                } else {
                    $frequency = ProductPriceFrequencyEnum::UNIQUE;
                }

                ProductPrice::updateOrCreate([
                    'stripe_price_id' => $price->id,
                ], [
                    'product_id' => $pr->id,
                    'frequency' => $frequency,
                    'price' => $price->unit_amount / 100,
                    'stripe_price_id' => $price->id,
                ]);


            }
        }
    }
}
