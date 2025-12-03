<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stream>
 */
class StreamFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'stream_url' => fake()->url() . '/stream.m3u8',
            'stream_type' => fake()->randomElement(['hls', 'mpegts', 'rtmp', 'http']),
            'category_id' => Category::factory(),
            'server_id' => null,
            'epg_channel_id' => fake()->optional()->word(),
            'logo_url' => fake()->optional()->imageUrl(100, 100),
            'stream_icon' => null,
            'is_active' => true,
            'is_hidden' => false,
            'sort_order' => fake()->numberBetween(0, 100),
            'custom_sid' => fake()->optional()->numerify('####'),
            'notes' => fake()->optional()->sentence(),
            'last_check_at' => fake()->optional()->dateTimeThisMonth(),
            'last_check_status' => fake()->randomElement(['online', 'offline', null]),
            'bitrate' => fake()->optional()->randomElement(['2000', '4000', '8000']),
            'resolution' => fake()->optional()->randomElement(['720p', '1080p', '4K']),
        ];
    }

    /**
     * Create an active stream.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'last_check_status' => 'online',
        ]);
    }

    /**
     * Create an offline stream.
     */
    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_check_status' => 'offline',
        ]);
    }

    /**
     * Assign to a specific category.
     */
    public function inCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Assign to a specific server.
     */
    public function onServer(Server $server): static
    {
        return $this->state(fn (array $attributes) => [
            'server_id' => $server->id,
        ]);
    }
}
