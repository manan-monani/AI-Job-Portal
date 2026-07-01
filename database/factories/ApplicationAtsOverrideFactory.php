<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApplicationAtsOverride>
 */
class ApplicationAtsOverrideFactory extends Factory
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
            'pipeline_stage_id' => \App\Models\PipelineStage::factory(),
            'overridden_by' => \App\Models\User::factory(),
            'original_passed' => fake()->boolean(),
            'overridden_passed' => fake()->boolean(),
            'reason' => fake()->paragraph(),
        ];
    }
}
