<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendStageTransitionEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $applicationId,
        public int $stageId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $application = \App\Models\JobApplication::with('jobPost')->find($this->applicationId);
        $stage = \App\Models\PipelineStage::with('emailTemplate')->find($this->stageId);

        if (! $application || ! $stage || ! $stage->emailTemplate) {
            return;
        }

        \Illuminate\Support\Facades\Mail::to($application->email)
            ->send(new \App\Mail\StageTransitionMail($application, $stage->emailTemplate));
    }
}
