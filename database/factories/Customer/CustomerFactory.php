<?php

namespace Database\Factories\Customer;

use App\Enum\Customer\CustomerTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer\Customer>
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
            'type_compte' => fake()->randomElement(CustomerTypeEnum::array()->pluck('value')->toArray()),
            'entreprise' => fake()->company(),
            'adresse' => fake()->address(),
            'code_postal' => fake()->postcode(),
            'ville' => fake()->city(),
            'pays' => fake()->country(),
            'tel' => fake()->phoneNumber(),
            'portable' => fake()->phoneNumber(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
