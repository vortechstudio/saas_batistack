<?php

namespace Database\Factories;

use App\Models\ExternalSyncLog;
use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExternalSyncLog>
 */
class ExternalSyncLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExternalSyncLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'system_name' => $this->faker->randomElement(['external_api', 'crm_system', 'billing_system']),
            'operation' => $this->faker->randomElement(['sync', 'export', 'import']),
            'entity_type' => $this->faker->randomElement(['customers', 'products', 'licenses', 'users']),
            'entity_id' => $this->faker->numberBetween(1, 1000),
            'status' => $this->faker->randomElement(SyncStatus::cases()),
            'request_data' => [
                'field1' => $this->faker->word(),
                'field2' => $this->faker->numberBetween(1, 100),
            ],
            'response_data' => [
                'success' => $this->faker->boolean(),
                'message' => $this->faker->sentence(),
            ],
            'error_message' => $this->faker->optional(0.2)->sentence(),
            'retry_count' => $this->faker->numberBetween(0, 3),
            'last_retry_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 week', 'now'),
            'started_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
            'completed_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the sync was successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SyncStatus::SUCCESS,
            'error_message' => null,
            'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the sync failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SyncStatus::FAILED,
            'error_message' => $this->faker->sentence(),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the sync is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SyncStatus::PENDING,
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }
}