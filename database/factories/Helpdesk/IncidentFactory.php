<?php

namespace Database\Factories\Helpdesk;

use App\Models\Helpdesk\Incident;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'title' => $this->faker->word(),
            'status' => $this->faker->word(),
            'impact' => $this->faker->word(),
            'occurred_at' => Carbon::now(),
            'resolved_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
