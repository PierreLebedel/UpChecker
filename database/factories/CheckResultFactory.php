<?php

namespace Database\Factories;

use App\Enums\CheckStatus;
use App\Models\CheckResult;
use App\Models\Monitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckResult>
 */
class CheckResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'monitor_id' => Monitor::factory(),
            'status' => CheckStatus::Up,
            'http_status' => 200,
            'response_time_ms' => fake()->numberBetween(20, 900),
            'checked_url' => fake()->url(),
            'checked_at' => now(),
        ];
    }

    public function down(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CheckStatus::Down,
            'http_status' => 500,
            'error_message' => 'Unexpected HTTP status 500.',
        ]);
    }

    public function timeout(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CheckStatus::Timeout,
            'http_status' => null,
            'response_time_ms' => null,
            'error_message' => 'The request timed out.',
        ]);
    }
}
