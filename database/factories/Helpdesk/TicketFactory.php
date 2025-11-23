<?php

namespace Database\Factories\Helpdesk;

use App\Models\Helpdesk\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'subject' => $this->faker->word(),
            'status' => $this->faker->word(),
            'priority' => $this->faker->word(),
            'category' => $this->faker->word(),
            'last_reply_at' => Carbon::now(),
            'closed_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
            'assigned_to' => User::factory(),
        ];
    }
}
