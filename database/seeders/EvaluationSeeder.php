<?php

namespace Database\Seeders;

use App\Models\CandidateStageStatus;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = User::where('email', 'manager@admin.com')->first();

        if (! $manager) {
            $this->command->error('Manager user not found!');

            return;
        }

        // Get jobs with pipelines
        $mappedJobs = JobPost::where('pipeline_enabled', true)
            ->whereHas('applications')
            ->whereHas('pipelineStages')
            ->get();

        foreach ($mappedJobs as $job) {
            // Fetch stages Ordered
            $stages = $job->pipelineStages()->orderBy('sort_order')->get();
            if ($stages->isEmpty()) {
                continue;
            }

            $sortingStage = $stages->where('type', 'sorting')->first();
            $assessmentStage = $stages->where('type', 'assessment')->first();
            $interviewStage = $stages->where('type', 'interview')->first();

            // Fetch applications for this job
            $applications = $job->applications;

            foreach ($applications as $index => $app) {
                // Different applicants, different progress

                // 1st Applicant: Passed Sorting, Passed Quiz, Waiting for Interview
                if ($index === 0 && $sortingStage && $assessmentStage && $interviewStage) {
                    $this->progressCandidate($app, $sortingStage, 'passed', null, $manager);
                    $this->progressCandidate($app, $assessmentStage, 'passed', 90, $manager);

                    // Currently in Interview stage
                    $app->update(['status' => 'in_progress']);
                }

                // 2nd Applicant: Rejected at Sorting
                elseif ($index === 1 && $sortingStage) {
                    $this->progressCandidate($app, $sortingStage, 'rejected', null, $manager);
                    $app->update(['status' => 'rejected']);
                }

                // 3rd Applicant: Failed Quiz
                elseif ($index === 2 && $sortingStage && $assessmentStage) {
                    $this->progressCandidate($app, $sortingStage, 'passed', null, $manager);
                    $this->progressCandidate($app, $assessmentStage, 'rejected', 60, $manager);
                    $app->update(['status' => 'rejected']);
                }

                // 4th Applicant: Hired!
                elseif ($index === 3 && $sortingStage && $assessmentStage && $interviewStage) {
                    $this->progressCandidate($app, $sortingStage, 'passed', null, $manager);
                    $this->progressCandidate($app, $assessmentStage, 'passed', 95, $manager);
                    $this->progressCandidate($app, $interviewStage, 'passed', null, $manager, 'Excellent cultural fit.');
                    $app->update(['status' => 'hired']);
                }
            }
        }

        $this->command->info('Evaluations seeded successfully.');
    }

    private function progressCandidate(JobApplication $app, PipelineStage $stage, string $status, ?int $score, User $manager, ?string $notes = null)
    {
        CandidateStageStatus::updateOrCreate(
            [
                'job_application_id' => $app->id,
                'pipeline_stage_id' => $stage->id,
            ],
            [
                'status' => $status,
                'score' => $score,
                'notes' => $notes,
                'actioned_by' => $manager->id,
                'actioned_at' => now(),
            ]
        );
    }
}
