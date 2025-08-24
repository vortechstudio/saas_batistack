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
            'type_compte' => $this->faker->randomElement(CustomerTypeEnum::array()->pluck('value')->toArray()),
            'entreprise' => $this->faker->company(),
            'adresse' => $this->faker->address(),
            'code_postal' => $this->faker->postcode(),
            'ville' => $this->faker->city(),
            'pays' => $this->faker->country(),
            'tel' => $this->faker->phoneNumber(),
            'portable' => $this->faker->phoneNumber(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
