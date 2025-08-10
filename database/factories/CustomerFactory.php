<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->randomElement(['FR', 'BE', 'CH', 'LU']),
            'siret' => fake()->numerify('##############'),
            'vat_number' => fake()->regexify('FR[0-9]{11}'),
            'status' => fake()->randomElement(CustomerStatus::cases())->value,
            'stripe_customer_id' => 'cus_' . fake()->regexify('[A-Za-z0-9]{14}'),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Client actif
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatus::ACTIVE->value,
        ]);
    }

    /**
     * Client inactif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatus::INACTIVE->value,
        ]);
    }

    /**
     * Client suspendu
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatus::SUSPENDED->value,
        ]);
    }

    /**
     * Client français
     */
    public function french(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'FR',
            'postal_code' => fake('fr_FR')->postcode(),
            'city' => fake('fr_FR')->city(),
            'siret' => fake('fr_FR')->siret(),
            'vat_number' => 'FR' . fake()->numerify('##########'),
        ]);
    }

    /**
     * Client belge
     */
    public function belgian(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'BE',
            'postal_code' => fake()->numerify('####'),
            'vat_number' => 'BE' . fake()->numerify('##########'),
        ]);
    }

    /**
     * Client sans Stripe
     */
    public function withoutStripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_customer_id' => null,
        ]);
    }

    /**
     * Associer à un utilisateur existant
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
