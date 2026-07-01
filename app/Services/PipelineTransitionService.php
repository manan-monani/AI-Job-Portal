<?php

namespace App\Services;

use App\Models\CandidateStageHistory;
use App\Models\CandidateStageStatus;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\DB;

class PipelineTransitionService
{
    public function __construct(protected MailCommunicationService $mailService) {}

    /**
     * Move multiple candidates from one stage to another.
     */
    public function moveCandidates(array $candidateIds, int $fromStageId, int $toStageId, bool $sendMail = false): void
    {
        DB::transaction(function () use ($candidateIds, $fromStageId, $toStageId, $sendMail) {
            $fromStage = PipelineStage::findOrFail($fromStageId);
            $toStage = PipelineStage::findOrFail($toStageId);

            // Forward-only validation (simple sort_order check)
            if ($toStage->sort_order <= $fromStage->sort_order) {
                throw new \Exception('Candidates can only be moved to a forward stage.');
            }

            foreach ($candidateIds as $appId) {
                $this->transitionSingleCandidate($appId, $fromStage, $toStage, $sendMail);
            }
        });
    }

    /**
     * Handle the transition for a single candidate.
     */
    protected function transitionSingleCandidate(int $appId, PipelineStage $fromStage, PipelineStage $toStage, bool $sendMail): void
    {
        $status = CandidateStageStatus::where('job_application_id', $appId)
            ->where('pipeline_stage_id', $fromStage->id)
            ->first();

        if (! $status) {
            return;
        }

        if ($status->status === 'rejected') {
            throw new \Exception("Cannot move candidate #{$appId} because they are rejected in the current stage.");
        }

        // 1. Record history for the old stage
        CandidateStageHistory::create([
            'candidate_stage_status_id' => $status->id,
            'from_status' => $status->status,
            'to_status' => 'passed',
            'changed_by' => auth()->id(),
            'notes' => "Moved to {$toStage->title}",
        ]);

        // 2. Update current stage status to passed
        $status->update(['status' => 'passed']);

        // 3. Create or update next stage status
        CandidateStageStatus::updateOrCreate(
            [
                'job_application_id' => $appId,
                'pipeline_stage_id' => $toStage->id,
            ],
            [
                'status' => 'in_progress',
                'actioned_by' => auth()->id(),
                'actioned_at' => now(),
            ]
        );

        // 4. Send email via centralized service if requested or auto-trigger is enabled
        $shouldSend = $sendMail || $toStage->send_mail_on_trigger;
        if ($shouldSend) {
            $application = \App\Models\JobApplication::find($appId);
            if ($application) {
                // Auto-hire when reaching the onboard stage
                if ($toStage->system_key === 'onboard_mail') {
                    $application->update(['status' => 'hired']);
                }

                $this->mailService->sendStageEmail(
                    application: $application,
                    stage: $toStage,
                    mode: $sendMail ? 'manual' : 'auto',
                    adminUserId: $sendMail ? auth()->id() : null,
                );
            }
        } else {
            // Even without mail, still mark as hired on onboard
            if ($toStage->system_key === 'onboard_mail') {
                $application = \App\Models\JobApplication::find($appId);
                $application?->update(['status' => 'hired']);
            }
        }
    }
}
