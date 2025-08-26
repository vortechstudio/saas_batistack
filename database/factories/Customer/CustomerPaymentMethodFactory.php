<?php

namespace Database\Factories\Customer;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer\CustomerPaymentMethod>
 */
class CustomerPaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stripe_payment_method_id' => $this->faker->uuid(),
            'is_active' => $this->faker->boolean(),
            'is_default' => $this->faker->boolean(),
            'customer_id' => Customer::factory(),
        ];
    }
}
