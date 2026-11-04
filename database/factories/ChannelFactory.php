<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Channel>
 */
class ChannelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => null,
            'name' => fake()->unique()->slug(2),
            'type' => 'topic',
            'is_private' => false,
            'is_archived' => false,
            'created_by' => null,
        ];
    }

    public function announcement(): static
    {
        return $this->state(fn () => ['name' => 'pengumuman', 'type' => 'announcement']);
    }

    public function direct(): static
    {
        return $this->state(fn () => ['type' => 'direct', 'is_private' => true]);
    }
}
