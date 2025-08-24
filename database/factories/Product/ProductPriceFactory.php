<?php

namespace Database\Factories\Product;

use App\Enum\Product\ProductPriceFrequencyEnum;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product\ProductPrice>
 */
class ProductPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'frequency' => ProductPriceFrequencyEnum::MONTHLY,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stripe_price_id' => $this->faker->uuid(),
            'product_id' => Product::factory(),
        ];
    }
}
