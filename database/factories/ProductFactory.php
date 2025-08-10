<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productTypes = ['Starter', 'Professional', 'Enterprise', 'Basic', 'Premium', 'Ultimate'];
        $productName = 'Batistack ' . fake()->randomElement($productTypes) . ' ' . fake()->unique()->numberBetween(1000, 9999);

        return [
            'name' => $productName,
            'slug' => Str::slug($productName),
            'description' => fake()->sentence(10),
            'base_price' => fake()->randomFloat(2, 19.99, 299.99),
            'billing_cycle' => fake()->randomElement(BillingCycle::cases())->value,
            'max_users' => fake()->optional(0.3)->numberBetween(1, 100),
            'max_projects' => fake()->optional(0.3)->numberBetween(5, 500),
            'storage_limit' => fake()->numberBetween(1, 1000),
            'is_active' => fake()->boolean(95), // 95% de chance d'être actif
            'is_featured' => fake()->boolean(30), // 30% de chance d'être en vedette
            'stripe_price_id' => 'price_' . fake()->regexify('[A-Za-z0-9]{24}'),
        ];
    }

    /**
     * Produit Starter
     */
    public function starter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Batistack Starter',
            'slug' => 'batistack-starter',
            'description' => 'Solution de base pour les petites entreprises du BTP',
            'base_price' => 49.99,
            'max_users' => 3,
            'max_projects' => 10,
            'storage_limit' => 5,
        ]);
    }

    /**
     * Produit Professional
     */
    public function professional(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Batistack Professional',
            'slug' => 'batistack-professional',
            'description' => 'Solution complète pour les entreprises en croissance',
            'base_price' => 99.99,
            'max_users' => 10,
            'max_projects' => 50,
            'storage_limit' => 25,
            'is_featured' => true,
        ]);
    }

    /**
     * Produit Enterprise
     */
    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Batistack Enterprise',
            'slug' => 'batistack-enterprise',
            'description' => 'Solution avancée pour les grandes entreprises',
            'base_price' => 199.99,
            'max_users' => null,
            'max_projects' => null,
            'storage_limit' => 100,
        ]);
    }

    /**
     * Produit mensuel
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => BillingCycle::MONTHLY->value,
        ]);
    }

    /**
     * Produit annuel
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => BillingCycle::YEARLY->value,
            'base_price' => $attributes['base_price'] * 10, // Prix annuel avec réduction
        ]);
    }

    /**
     * Produit en vedette
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Produit inactif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
