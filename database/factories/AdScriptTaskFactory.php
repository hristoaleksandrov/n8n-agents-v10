<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\AdScriptTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdScriptTask>
 */
class AdScriptTaskFactory extends Factory
{
    protected $model = AdScriptTask::class;

    public function definition(): array
    {
        return [
            'reference_script' => $this->faker->paragraphs(2, true),
            'outcome_description' => $this->faker->sentence(),
            'status' => TaskStatus::Pending,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => TaskStatus::Pending]);
    }

    public function processing(): static
    {
        return $this->state(fn () => ['status' => TaskStatus::Processing]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Completed,
            'new_script' => $this->faker->paragraphs(2, true),
            'analysis' => $this->faker->paragraph(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Failed,
            'error_message' => $this->faker->sentence(),
        ]);
    }
}
