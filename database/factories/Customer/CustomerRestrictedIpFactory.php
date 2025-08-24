<?php

namespace Database\Factories\Customer;

use App\Enum\Customer\CustomerRestrictedIpTypeEnum;
use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer\CustomerRestrictedIp>
 */
class CustomerRestrictedIpFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'ip' => $this->faker->ipv4,
            'authorized' => $this->faker->randomElement(CustomerRestrictedIpTypeEnum::array()->pluck('value')->toArray()),
            'alert' => $this->faker->boolean,
        ];
    }
}
