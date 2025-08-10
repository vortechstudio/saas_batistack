<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(NotificationType::cases()),
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'data' => [
                'title' => $this->faker->sentence(4),
                'message' => $this->faker->paragraph(),
                'entity_id' => $this->faker->numberBetween(1, 1000),
                'entity_type' => $this->faker->randomElement(['Invoice', 'Payment', 'License']),
            ],
            'priority' => $this->faker->numberBetween(1, 5),
            'channels' => [$this->faker->randomElement(['email', 'sms', 'push', 'database'])],
            'level' => $this->faker->randomElement(['info', 'warning', 'error', 'success']),
            'read_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 month', 'now'),
            'scheduled_at' => $this->faker->optional(0.3)->dateTimeBetween('now', '+1 week'),
            'sent_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the notification is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the notification is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => null,
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 week'),
        ]);
    }

    /**
     * Set a specific notification type.
     */
    public function ofType(NotificationType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Set high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 5,
            'type' => $this->faker->randomElement([
                NotificationType::SECURITY_ALERT,
                NotificationType::SYSTEM_ALERT,
                NotificationType::LICENSE_EXPIRED,
            ]),
        ]);
    }
}