<?php

namespace Database\Factories\Helpdesk;

use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TicketMessageFactory extends Factory
{
    protected $model = TicketMessage::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->word(),
            'attachments' => $this->faker->words(),
            'is_internal_note' => $this->faker->boolean(),
            'u' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
        ];
    }
}
