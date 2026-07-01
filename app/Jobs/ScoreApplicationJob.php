<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScoreApplicationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $applicationId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(\App\Services\AtsScoringService $scoringService): void
    {
        $application = \App\Models\JobApplication::with('jobPost.pipelineStages')->find($this->applicationId);

        if (! $application || ! $application->jobPost) {
            return;
        }

        // Cancellation check
        if ($application->ats_state !== 'processing' && $application->ats_state !== 'pending') {
            \Illuminate\Support\Facades\Log::info("ScoreApplicationJob skipped for Application {$application->id}: Scan was stopped/reset.");

            return;
        }

        // Only evaluate on sorting stages
        $sortingStages = $application->jobPost->pipelineStages->where('type', 'sorting');

        foreach ($sortingStages as $stage) {
            try {
                $scoringService->scoreApplication($application, $stage);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error(
                    "Error scoring application {$this->applicationId} for stage {$stage->id}: ".$e->getMessage()
                );
                $application->update(['ats_state' => 'failed']);
            }
        }
    }
}
