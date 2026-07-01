<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobPost>
 */
class JobPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => \App\Models\Department::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraphs(3, true),
            'salary_type' => fake()->randomElement(['negotiable', 'non-negotiable']),
            'min_salary' => fake()->numberBetween(30000, 50000),
            'max_salary' => fake()->numberBetween(60000, 100000),
            'min_experience' => fake()->numberBetween(0, 2),
            'max_experience' => fake()->numberBetween(3, 10),
            'job_type' => fake()->randomElement(['onsite', 'remote', 'hybrid']),
            'location' => fake()->city().', '.fake()->country(),
            'deadline' => fake()->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d'),
            'employment_type' => fake()->randomElement(['full-time', 'part-time', 'contract', 'internship']),
            'status' => fake()->randomElement(['draft', 'published', 'hidden']),
        ];
    }
}
