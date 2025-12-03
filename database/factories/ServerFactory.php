<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Server>
 */
class ServerFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' Server',
            'base_url' => fake()->url(),
            'rtmp_url' => null,
            'http_port' => 80,
            'https_port' => 443,
            'rtmp_port' => 1935,
            'is_active' => true,
            'is_primary' => false,
            'weight' => fake()->numberBetween(1, 10),
            'max_connections' => fake()->numberBetween(100, 1000),
            'current_connections' => 0,
            'notes' => fake()->optional()->sentence(),
            'last_check_at' => now(),
            'last_check_status' => 'online',
        ];
    }

    /**
     * Create a primary server.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    /**
     * Create an inactive server.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'last_check_status' => 'offline',
        ]);
    }
}
