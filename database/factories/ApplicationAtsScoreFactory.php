<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApplicationAtsScore>
 */
class ApplicationAtsScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_application_id' => \App\Models\JobApplication::factory(),
            'job_post_id' => \App\Models\JobPost::factory(),
            'pipeline_stage_id' => \App\Models\PipelineStage::factory(),
            'total_score' => fake()->randomFloat(2, 0, 100),
            'passed' => fake()->boolean(),
            'pass_reason' => fake()->sentence(),
            'criteria_breakdown' => [],
            'scoring_version' => '1.0.0',
            'scored_at' => now(),
        ];
    }
}
