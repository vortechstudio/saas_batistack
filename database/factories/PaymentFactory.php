<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'invoice_id' => Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 2000),
            'currency' => 'EUR',
            'payment_method' => $this->faker->randomElement(PaymentMethod::cases()),
            'status' => $this->faker->randomElement(PaymentStatus::cases()),
            'stripe_payment_intent_id' => 'pi_' . $this->faker->unique()->numerify('##########'),
            'stripe_charge_id' => 'ch_' . $this->faker->unique()->numerify('##########'),
            'processed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'metadata' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the payment succeeded.
     */
    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::SUCCEEDED,
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::FAILED,
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::PENDING,
        ]);
    }
}