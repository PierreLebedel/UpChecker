<?php

namespace Database\Factories;

use App\Enums\MonitorStatus;
use App\Models\Monitor;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Monitor>
 */
class MonitorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->domainName(),
            'url' => fake()->url(),
            'enabled' => true,
            'interval_minutes' => 5,
            'timeout_seconds' => 10,
            'expected_http_status' => 200,
            'current_status' => MonitorStatus::Unknown,
            'next_check_at' => now(),
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => false,
        ]);
    }

    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_check_at' => now()->subMinute(),
        ]);
    }

    public function notDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_check_at' => now()->addMinute(),
        ]);
    }

    public function up(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_status' => MonitorStatus::Up,
            'last_checked_at' => now(),
            'last_success_at' => now(),
        ]);
    }

    public function down(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_status' => MonitorStatus::Down,
            'last_checked_at' => now(),
            'last_failure_at' => now(),
        ]);
    }
}
