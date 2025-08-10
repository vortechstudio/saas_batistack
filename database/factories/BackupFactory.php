<?php

namespace Database\Factories;

use App\Models\Backup;
use App\Enums\BackupStatus;
use App\Enums\BackupType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Backup>
 */
class BackupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Backup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $completedAt = $this->faker->optional(0.8)->dateTimeBetween($startedAt, 'now');

        return [
            'name' => 'backup-' . $this->faker->date() . '-' . $this->faker->time('His'),
            'type' => $this->faker->randomElement(BackupType::cases()),
            'storage_driver' => $this->faker->randomElement(['local', 's3', 'backup']),
            'file_path' => 'backups/' . $this->faker->uuid() . '.zip',
            'status' => $this->faker->randomElement(BackupStatus::cases()),
            'file_size' => $this->faker->numberBetween(1024, 1073741824), // 1KB to 1GB
            'metadata' => [],
            'error_message' => null,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the backup is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BackupStatus::COMPLETED,
            'completed_at' => $this->faker->dateTimeBetween($attributes['started_at'], 'now'),
        ]);
    }

    /**
     * Indicate that the backup failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BackupStatus::FAILED,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the backup is running.
     */
    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BackupStatus::RUNNING,
            'completed_at' => null,
        ]);
    }
}