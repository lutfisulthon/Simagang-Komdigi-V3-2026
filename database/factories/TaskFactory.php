<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'number' => fake()->unique()->numberBetween(1, 100000),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
            'created_by' => null,
            'start_at' => now(),
            'due_at' => now()->addDays(fake()->numberBetween(1, 14)),
            'position' => 0,
        ];
    }

    public function status(TaskStatus $status): static
    {
        return $this->state(fn () => [
            'status' => $status,
            'completed_at' => $status === TaskStatus::Done ? now() : null,
            'score' => $status === TaskStatus::Done ? fake()->numberBetween(70, 100) : null,
            'feedback' => in_array($status, [TaskStatus::Done, TaskStatus::Revision], true)
                ? fake()->sentence()
                : null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => ['due_at' => now()->subDays(2)]);
    }
}
