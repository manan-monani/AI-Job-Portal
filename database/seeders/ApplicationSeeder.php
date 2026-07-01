<?php

namespace Database\Seeders;

use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $candidate1 = User::where('email', 'candidate1@gmail.com')->first();
        $candidate2 = User::where('email', 'candidate2@gmail.com')->first();

        if (! $candidate1 || ! $candidate2) {
            $this->command->error('Candidates not found! Please run UserSeeder first.');

            return;
        }

        // Prepare dummy resume file in storage
        $sourcePdf = database_path('seeders/dummy_image/sample_cv.pdf');
        $destPath = 'resumes/sample_cv.pdf';

        if (File::exists($sourcePdf)) {
            if (! Storage::disk('public')->exists('resumes')) {
                Storage::disk('public')->makeDirectory('resumes');
            }
            Storage::disk('public')->put($destPath, File::get($sourcePdf));
        }

        // Jobs with pipelines enabled
        $pipelineJobs = JobPost::where('pipeline_enabled', true)->get();
        // Jobs without pipelines
        $directJobs = JobPost::where('pipeline_enabled', false)->get();

        // 1. Candidate 1 applies to pipeline jobs
        if ($pipelineJobs->count() > 0) {
            $job = $pipelineJobs->first();
            $this->createApplication($job, $candidate1, 'Jane', 'Doe', $destPath, 'Applied for Senior role. I have 6 years of Vue experience.');

            if ($pipelineJobs->count() > 1) {
                // Another guest applicant
                $this->createApplication($pipelineJobs->last(), null, 'John', 'Smith', $destPath, 'Highly interested in the marketing manager position.');
            }
        }

        // 2. Candidate 2 applies to direct jobs
        if ($directJobs->count() > 0) {
            $job = $directJobs->first();
            $this->createApplication($job, $candidate2, 'Mark', 'Taylor', $destPath, 'Frontend engineer application.');
        }

        // 3. Guest applications to generate some volume
        if ($pipelineJobs->count() > 0) {
            $job = $pipelineJobs->first();
            $this->createApplication($job, null, 'Emily', 'Chen', $destPath, 'Experienced Product Designer.');
            $this->createApplication($job, null, 'Michael', 'Ross', $destPath, 'Love your brand! Would love to join.');
        }

        $this->command->info('Job Applications seeded successfully.');
    }

    private function createApplication(JobPost $job, ?User $user, string $firstName, string $lastName, string $resumePath, string $message)
    {
        $email = $user ? $user->email : strtolower($firstName.'.'.$lastName.'@example.com');
        $phone = '+1234567890';

        // Prevent duplicates
        $existing = JobApplication::where('job_post_id', $job->id)
            ->where('email', $email)
            ->first();

        if ($existing) {
            return $existing;
        }

        $application = JobApplication::create([
            'job_post_id' => $job->id,
            'user_id' => $user?->id,
            'name' => $firstName.' '.$lastName,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'status' => 'pending',
        ]);

        if ($resumePath && Storage::disk('public')->exists($resumePath)) {
            Resume::create([
                'user_id' => $user?->id,
                'job_application_id' => $application->id,
                'job_post_id' => $job->id,
                'file_path' => $resumePath,
                'cv_title' => 'Resume.pdf',
            ]);
        }

        return $application;
    }
}
