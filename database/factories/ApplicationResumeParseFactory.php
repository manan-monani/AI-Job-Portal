<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApplicationResumeParse>
 */
class ApplicationResumeParseFactory extends Factory
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
            'raw_text' => fake()->paragraphs(5, true),
            'parsed_json' => [
                'raw_normalized' => fake()->paragraphs(3, true),
                'skills' => fake()->words(5),
                'inferred_experience_years' => fake()->numberBetween(1, 10),
                'job_titles' => [fake()->jobTitle()],
                'locations' => [],
            ],
            'parser_version' => '1.0.0',
            'status' => $this->faker->randomElement(['pending', 'processing', 'done', 'failed']),
            'error_message' => null,
            'parsed_at' => now(),
        ];
    }
}
