<?php

namespace Database\Factories\Helpdesk;

use App\Models\Helpdesk\SystemComponents;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SystemComponentsFactory extends Factory
{
    protected $model = SystemComponents::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'group' => $this->faker->word(),
            'status' => $this->faker->word(),
            'description' => $this->faker->text(),
            'order' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
