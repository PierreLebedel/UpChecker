<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Endpoint>
 */
class EndpointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url'                  => fake()->url(),
            'expected_status_code' => 200,
            'timeout'              => mt_rand(5, 30),
            'follow_redirects'     => fake()->boolean(),
        ];
    }
}
