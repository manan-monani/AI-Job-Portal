<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\JobPost;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $engineering = Department::where('name', 'Engineering')->first();
        $marketing = Department::where('name', 'Marketing')->first();
        $product = Department::where('name', 'Product')->first();
        $business = Department::where('name', 'Business')->first();
        $hr = Department::where('name', 'Human Resources')->first();

        $jobs = [
            [
                'title' => 'Senior Full Stack Developer (Laravel/Vue)',
                'department_id' => $engineering->id ?? null,
                'description' => '<h3>About the Role</h3><p>We are seeking a highly skilled Senior Full Stack Developer to lead architecture and development of scalable web applications. You will be instrumental in making critical technical decisions and guiding junior engineers.</p><h3>Key Responsibilities</h3><ul><li>Design, develop, and maintain robust backend systems using Laravel.</li><li>Build interactive and performant frontend components with Vue.js/Inertia.</li><li>Optimize database queries and system architecture for high traffic.</li></ul>',
                'salary_type' => 'non-negotiable',
                'salary_period' => 'monthly',
                'min_salary' => 8000,
                'max_salary' => 12000,
                'min_experience' => 5,
                'max_experience' => 10,
                'job_type' => 'hybrid',
                'location' => 'San Francisco, CA (Hybrid)',
                'deadline' => now()->addDays(30),
                'employment_type' => 'full-time',
                'status' => 'published',
                'pipeline_enabled' => true,
                'is_cv_required' => true,
                'tags' => ['JavaScript', 'Laravel', 'PHP'],
            ],
            [
                'title' => 'Digital Marketing Manager',
                'department_id' => $marketing->id ?? null,
                'description' => '<h3>About the Role</h3><p>Drive our digital presence and customer acquisition strategies globally. The ideal candidate has deep expertise in managing large ad spends and optimizing conversion funnels.</p><h3>What You Will Do</h3><ul><li>Manage end-to-end performance marketing campaigns across Google Ads, Meta, and LinkedIn.</li><li>Analyze campaign performance data to optimize ROI and CAC.</li><li>Collaborate with the brand team to ensure cohesive messaging.</li></ul>',
                'salary_type' => 'negotiable',
                'salary_period' => 'yearly',
                'min_salary' => null,
                'max_salary' => null,
                'min_experience' => 4,
                'max_experience' => 7,
                'job_type' => 'remote',
                'location' => 'Global (Remote)',
                'deadline' => now()->addDays(45),
                'employment_type' => 'full-time',
                'status' => 'published',
                'pipeline_enabled' => true,
                'is_cv_required' => true,
                'tags' => ['Marketing', 'Paid Advertising', 'Analytics & Tracking'],
            ],
            [
                'title' => 'Frontend React Engineer',
                'department_id' => $engineering->id ?? null,
                'description' => '<h3>Overview</h3><p>We are expanding our frontend team to rebuild our core client dashboard using modern React and Next.js practices. You must be obsessed with web vitals, accessibility, and component-driven design.</p>',
                'salary_type' => 'non-negotiable',
                'salary_period' => 'monthly',
                'min_salary' => 6000,
                'max_salary' => 9000,
                'min_experience' => 3,
                'max_experience' => 5,
                'job_type' => 'remote',
                'location' => 'Remote (US/EU Timezones)',
                'deadline' => now()->addDays(20),
                'employment_type' => 'full-time',
                'status' => 'published',
                'pipeline_enabled' => false,
                'is_cv_required' => true,
                'tags' => ['JavaScript', 'React', 'Next.js', 'UI/UX'],
            ],
            [
                'title' => 'Enterprise Sales Executive',
                'department_id' => $business->id ?? null,
                'description' => '<h3>The Opportunity</h3><p>Join our high-performing enterprise sales team. You will target Fortune 500 companies, conduct product demonstrations, and close complex six-figure deals.</p>',
                'salary_type' => 'negotiable',
                'salary_period' => 'yearly',
                'min_salary' => null,
                'max_salary' => null,
                'min_experience' => 5,
                'max_experience' => 12,
                'job_type' => 'onsite',
                'location' => 'New York, NY',
                'deadline' => now()->addDays(15),
                'employment_type' => 'full-time',
                'status' => 'published',
                'pipeline_enabled' => false,
                'is_cv_required' => true,
                'tags' => ['Sales', 'Communication'],
            ],
            [
                'title' => 'Product Designer (UI/UX)',
                'department_id' => $product->id ?? null,
                'description' => '<h3>Why Us?</h3><p>Shape the future of our product interface. We need a visionary designer who can translate complex user flows into beautiful, intuitive, and accessible experiences.</p>',
                'salary_type' => 'non-negotiable',
                'salary_period' => 'monthly',
                'min_salary' => 7000,
                'max_salary' => 11000,
                'min_experience' => 4,
                'max_experience' => 8,
                'job_type' => 'hybrid',
                'location' => 'London, UK (Hybrid)',
                'deadline' => now()->addDays(60),
                'employment_type' => 'full-time',
                'status' => 'published',
                'pipeline_enabled' => true,
                'is_cv_required' => true,
                'tags' => ['UI/UX', 'Communication'],
            ],
            [
                'title' => 'HR Technical Recruiter',
                'department_id' => $hr->id ?? null,
                'description' => '<h3>About the Role</h3><p>Help us scale our engineering team. You will be responsible for sourcing, screening, and hiring top-tier technical talent globally.</p>',
                'salary_type' => 'non-negotiable',
                'salary_period' => 'monthly',
                'min_salary' => 6000,
                'max_salary' => 6000,
                'min_experience' => 2,
                'max_experience' => 5,
                'job_type' => 'hybrid',
                'location' => 'Austin, TX',
                'deadline' => now()->subDays(2), // Closed job state
                'employment_type' => 'full-time',
                'status' => 'published',
                'pipeline_enabled' => false,
                'is_cv_required' => true,
                'tags' => ['Communication', 'Branding'],
            ],
        ];

        foreach ($jobs as $jobData) {
            if (! $jobData['department_id']) {
                continue;
            }

            $tagsToAttach = $jobData['tags'];
            unset($jobData['tags']);

            $job = JobPost::updateOrCreate(
                ['title' => $jobData['title']],
                $jobData
            );

            $tagIds = Tag::whereIn('name', $tagsToAttach)->pluck('id');
            $job->tags()->sync($tagIds);

            // Seed default system stages if they do not exist
            if ($job->pipelineStages()->where('is_system', true)->count() === 0) {
                app(\App\Services\PipelineService::class)->seedDefaultStages($job);
            }
        }

        $this->command->info('Jobs seeded successfully.');
    }
}
