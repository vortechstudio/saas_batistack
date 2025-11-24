<?php

namespace Database\Factories\Helpdesk;

use App\Models\Helpdesk\StatusSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class StatusSubscriberFactory extends Factory
{
    protected $model = StatusSubscriber::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'verified_at' => Carbon::now(),
            'verification_token' => Str::random(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
