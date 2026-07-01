<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PipelineStage>
 */
class PipelineStageFactory extends Factory
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
            'type' => fake()->randomElement(['initial', 'sorting', 'interview', 'offer']),
            'subtype' => null,
            'title' => fake()->words(2, true),
            'instructions' => fake()->paragraph(),
            'sort_order' => 1,
            'is_system' => false,
            'is_enabled' => true,
            'config' => [],
        ];
    }
}
