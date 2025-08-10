<?php

namespace Database\Factories;

use App\Enums\LicenseStatus;
use App\Models\Customer;
use App\Models\License;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Récupérer un produit existant ou créer un client existant
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $customer = Customer::inRandomOrder()->first() ?? Customer::factory()->create();

        $startsAt = $this->faker->dateTimeBetween('-1 year', 'now');
        $expiresAt = $this->faker->dateTimeBetween($startsAt, '+2 years');

        return [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'license_key' => 'LIC-' . strtoupper($this->faker->bothify('????-????-????-????')),
            'status' => $this->faker->randomElement(LicenseStatus::cases()),
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'max_users' => $product->max_users ?? $this->faker->numberBetween(1, 50),
            'current_users' => $this->faker->numberBetween(0, $product->max_users ?? 10),
            'last_used_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Licence active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::ACTIVE,
            'starts_at' => now()->subMonths(rand(1, 6)),
            'expires_at' => now()->addMonths(rand(6, 24)),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::EXPIRED,
            'starts_at' => now()->subYear(),
            'expires_at' => now()->subMonths(rand(1, 6)),
            'current_users' => 0,
            'last_used_at' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::SUSPENDED,
            'starts_at' => now()->subMonths(rand(3, 12)),
            'expires_at' => now()->addMonths(rand(1, 12)),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::CANCELLED,
            'starts_at' => now()->subMonths(rand(6, 18)),
            'expires_at' => now()->subMonths(rand(1, 6)),
            'current_users' => 0,
        ]);
    }

    public function recentlyUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => now()->subHours(rand(1, 24)),
        ]);
    }

    public function neverUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => null,
            'current_users' => 0,
        ]);
    }

    public function atCapacity(): static
    {
        return $this->state(function (array $attributes) {
            $maxUsers = $attributes['max_users'] ?? 10;
            return [
                'current_users' => $maxUsers,
            ];
        });
    }

    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
            'status' => LicenseStatus::ACTIVE,
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subDays(rand(1, 15)),
            'expires_at' => now()->addDays(rand(15, 30)),
            'status' => LicenseStatus::ACTIVE,
        ]);
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'max_users' => $product->max_users,
        ]);
    }
}
