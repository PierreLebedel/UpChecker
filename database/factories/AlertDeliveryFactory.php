<?php

namespace Database\Factories;

use App\Enums\AlertChannel;
use App\Enums\AlertDeliveryStatus;
use App\Models\AlertDelivery;
use App\Models\CheckResult;
use App\Models\Monitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlertDelivery>
 */
class AlertDeliveryFactory extends Factory
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
            'check_result_id' => CheckResult::factory(),
            'channel' => AlertChannel::Mail,
            'recipient' => fake()->safeEmail(),
            'status' => AlertDeliveryStatus::Pending,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AlertDeliveryStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AlertDeliveryStatus::Failed,
            'error_message' => 'Mail transport failed.',
        ]);
    }
}
