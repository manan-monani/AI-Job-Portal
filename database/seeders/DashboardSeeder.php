<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = \App\Models\Department::all();
        if ($departments->isEmpty()) {
            $departments = \App\Models\Department::factory()->count(5)->create();
        }

        $interviewer = \App\Models\User::where('type', 'admin')->first();
        if (! $interviewer) {
            $interviewer = \App\Models\User::factory()->create(['type' => 'admin']);
        }

        // 1. Create Jobs with different statuses and timeframes
        // Jobs from last week
        \App\Models\JobPost::factory()->count(5)->create([
            'status' => 'published',
            'created_at' => now()->subDays(10),
        ]);

        // Jobs from this week
        $thisWeekJobs = \App\Models\JobPost::factory()->count(8)->create([
            'status' => 'published',
            'created_at' => now()->subDays(2),
        ]);

        // Draft and Hidden jobs
        \App\Models\JobPost::factory()->count(3)->create(['status' => 'draft']);
        \App\Models\JobPost::factory()->count(2)->create(['status' => 'hidden']);

        // 2. Create Applications across stages
        $statuses = ['pending', 'applied', 'screening', 'shortlisted', 'interview', 'rejected', 'hired'];

        foreach ($thisWeekJobs as $job) {
            foreach ($statuses as $status) {
                $count = rand(1, 3);
                \App\Models\JobApplication::factory()->count($count)->create([
                    'job_post_id' => $job->id,
                    'status' => $status,
                    'created_at' => now()->subDays(rand(0, 5)),
                ]);
            }
        }

        // 3. Create Stalled Candidates (pending for > 7 days)
        $stalledJob = \App\Models\JobPost::factory()->create(['title' => 'Stalled Candidate Job']);
        \App\Models\JobApplication::factory()->count(5)->create([
            'job_post_id' => $stalledJob->id,
            'status' => 'pending',
            'created_at' => now()->subDays(15),
            'updated_at' => now()->subDays(15),
        ]);

        // 4. Create Interviews
        $interviewApplications = \App\Models\JobApplication::where('status', 'interview')->get();

        foreach ($interviewApplications as $application) {
            \App\Models\Interview::create([
                'job_application_id' => $application->id,
                'interviewer_id' => $interviewer->id,
                'scheduled_at' => now()->addDays(rand(0, 7))->setHour(rand(9, 17)),
                'location' => 'Office / Zoom',
                'status' => 'scheduled',
            ]);
        }

        // Past interviews
        $shortlistedApps = \App\Models\JobApplication::where('status', 'shortlisted')->take(5)->get();
        foreach ($shortlistedApps as $application) {
            \App\Models\Interview::create([
                'job_application_id' => $application->id,
                'interviewer_id' => $interviewer->id,
                'scheduled_at' => now()->subDays(rand(1, 5)),
                'location' => 'Office',
                'status' => 'completed',
            ]);
        }
    }
}
