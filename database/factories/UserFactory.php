<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user has two-factor authentication enabled.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['code1', 'code2', 'code3'],
        ]);
    }

    /**
     * Indicate that the user is locked.
     */
    public function locked(int $minutes = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'locked_until' => now()->addMinutes($minutes),
            'failed_login_attempts' => 5,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => fake()->unique()->userName() . '@batistack.com',
        ]);
    }

    /**
     * Indicate that the user has recent login activity.
     */
    public function withRecentLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
            'last_login_ip' => fake()->ipv4(),
        ]);
    }
}
