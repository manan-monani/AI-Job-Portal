<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SortingCriterion>
 */
class SortingCriterionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_post_id' => \App\Models\JobPost::factory(),
            'pipeline_stage_id' => \App\Models\PipelineStage::factory(),
            'type' => fake()->randomElement(['experience', 'skill', 'title', 'location', 'education']),
            'operator' => fake()->randomElement(['gte', 'contains', 'equals']),
            'value' => fake()->word(),
            'weight' => fake()->numberBetween(1, 10),
            'is_required' => fake()->boolean(20),
            'is_active' => true,
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
