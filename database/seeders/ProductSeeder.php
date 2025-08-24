<?php

namespace Database\Seeders;

use App\Enum\Product\ProductCategoryEnum;
use App\Models\Product\Product;
use App\Models\Product\ProductPrice;
use App\Services\Stripe\ProductService;
use App\Enum\Product\ProductPriceFrequencyEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = app(ProductService::class)->listWithPrices();

        foreach ($products as $product)
        {
            $category = match($product->metadata->category) {
                'modules' => ProductCategoryEnum::MODULE,
                'options' => ProductCategoryEnum::OPTION,
                'license' => ProductCategoryEnum::LICENSE,
                'support' => ProductCategoryEnum::SUPPORT
            };

            $pr = Product::create([
                'name' => $product->name,
                'slug' => $product->metadata->slug,
                'category' => $category,
                'description' => $product->description,
                'stripe_product_id' => $product->id,
            ]);

            foreach ($product->prices as $price)
            {
                // Mapper la fréquence Stripe vers l'enum
                $frequency = match($price->recurring->interval) {
                    'month' => ProductPriceFrequencyEnum::MONTHLY,
                    'year' => ProductPriceFrequencyEnum::ANNUAL,
                    default => ProductPriceFrequencyEnum::UNIQUE,
                };

                ProductPrice::create([
                    'product_id' => $pr->id,
                    'frequency' => $frequency,
                    'price' => $price->unit_amount / 100,
                    'stripe_price_id' => $price->id,
                ]);
            }
        }
    }
}
