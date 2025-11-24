<?php

namespace Database\Factories\Helpdesk;

use App\Models\Helpdesk\Incident;
use App\Models\Helpdesk\IncidentUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class IncidentUpdateFactory extends Factory
{
    protected $model = IncidentUpdate::class;

    public function definition(): array
    {
        return [
            'status' => $this->faker->word(),
            'message' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'incident_id' => Incident::factory(),
        ];
    }
}
