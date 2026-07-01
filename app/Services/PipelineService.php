<?php

namespace App\Services;

use App\Models\CandidateStageHistory;
use App\Models\CandidateStageStatus;
use App\Models\JobPost;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PipelineService
{
    /**
     * Get all stages for a job with their relationships.
     */
    public function getStagesForJob(JobPost $job): \Illuminate\Database\Eloquent\Collection
    {
        return $job->pipelineStages()
            ->with(['interviewers:id,name,email', 'criteria', 'emailTemplate', 'quizQuestions.options'])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Create the two mandatory system stages for a new job.
     */
    public function seedDefaultStages(JobPost $job): void
    {
        $job->pipelineStages()->createMany([
            [
                'type' => 'system',
                'title' => 'CV Received Confirmation Mail',
                'sort_order' => 1,
                'is_system' => true,
                'system_key' => 'cv_received_mail',
                'is_enabled' => true,
                'send_mail_on_trigger' => true,
            ],
            [
                'type' => 'system',
                'title' => 'Onboard Confirmation Mail',
                'sort_order' => 999,
                'is_system' => true,
                'system_key' => 'onboard_mail',
                'is_enabled' => true,
                'send_mail_on_trigger' => true,
            ],
        ]);
    }

    /**
     * Sync the entire pipeline configuration for a job.
     *
     * @param  array<int, array{id?: int, type: string, subtype?: string, title: string, instructions?: string, config?: array, sort_order: int, is_enabled?: bool, send_mail_on_trigger?: bool, interviewer_ids?: int[], criteria?: array}>  $stagesData
     */
    public function syncStages(JobPost $job, array $stagesData): void
    {
        DB::transaction(function () use ($job, $stagesData) {
            $existingStageIds = $job->pipelineStages()->pluck('id')->toArray();
            $submittedIds = [];

            foreach ($stagesData as $stageData) {
                $stageId = $stageData['id'] ?? null;

                if ($stageId && in_array($stageId, $existingStageIds)) {
                    // Update existing stage
                    $stage = PipelineStage::find($stageId);

                    if ($stage->is_system) {
                        // System stages: only update is_enabled and sort_order
                        $stage->update([
                            'is_enabled' => $stageData['is_enabled'] ?? $stage->is_enabled,
                            'sort_order' => $stageData['sort_order'],
                        ]);
                    } else {
                        $stage->update([
                            'type' => $stageData['type'],
                            'subtype' => $stageData['subtype'] ?? null,
                            'title' => $stageData['title'],
                            'instructions' => $stageData['instructions'] ?? null,
                            'config' => $stageData['config'] ?? null,
                            'sort_order' => $stageData['sort_order'],
                            'is_enabled' => $stageData['is_enabled'] ?? true,
                            'send_mail_on_trigger' => $stageData['send_mail_on_trigger'] ?? false,
                        ]);
                    }

                    $submittedIds[] = $stageId;
                } else {
                    // Create new custom stage (never create system stages here)
                    $stage = $job->pipelineStages()->create([
                        'type' => $stageData['type'],
                        'subtype' => $stageData['subtype'] ?? null,
                        'title' => $stageData['title'],
                        'instructions' => $stageData['instructions'] ?? null,
                        'config' => $stageData['config'] ?? null,
                        'sort_order' => $stageData['sort_order'],
                        'is_system' => false,
                        'is_enabled' => $stageData['is_enabled'] ?? true,
                        'send_mail_on_trigger' => $stageData['send_mail_on_trigger'] ?? false,
                    ]);

                    $submittedIds[] = $stage->id;
                }

                // Sync interviewers
                if (isset($stageData['interviewer_ids'])) {
                    $stage->interviewers()->sync($stageData['interviewer_ids']);
                }

                // Sync criteria (for sorting stages)
                if (isset($stageData['criteria']) && $stageData['type'] === 'sorting') {
                    $stage->criteria()->delete();
                    foreach ($stageData['criteria'] as $criterion) {
                        $stage->criteria()->create([
                            'label' => $criterion['label'],
                            'weight' => $criterion['weight'] ?? 1,
                        ]);
                    }
                }

                // Sync quiz questions (for assessment+quiz stages)
                if ($stageData['type'] === 'assessment' && ($stageData['subtype'] ?? null) === 'quiz' && isset($stageData['quiz_questions'])) {
                    $stage->quizQuestions()->delete();
                    foreach ($stageData['quiz_questions'] as $qIndex => $questionData) {
                        $question = $stage->quizQuestions()->create([
                            'question' => $questionData['question'],
                            'sort_order' => $qIndex + 1,
                        ]);

                        foreach ($questionData['options'] ?? [] as $oIndex => $optionData) {
                            $question->options()->create([
                                'option_text' => $optionData['option_text'],
                                'is_correct' => $optionData['is_correct'] ?? false,
                                'sort_order' => $oIndex + 1,
                            ]);
                        }
                    }
                }
            }

            // Delete removed custom stages (never delete system stages)
            $job->pipelineStages()
                ->where('is_system', false)
                ->whereNotIn('id', $submittedIds)
                ->delete();
        });
    }

    /**
     * Toggle a system stage enabled/disabled.
     */
    public function toggleSystemStage(PipelineStage $stage, bool $enabled): bool
    {
        if (! $stage->is_system) {
            return false;
        }

        return $stage->update(['is_enabled' => $enabled]);
    }

    /**
     * Move a candidate to a new status within a stage.
     */
    public function moveCandidate(
        CandidateStageStatus $status,
        string $newStatus,
        User $actor,
        ?string $notes = null
    ): CandidateStageStatus {
        return DB::transaction(function () use ($status, $newStatus, $actor, $notes) {
            $oldStatus = $status->status;

            $status->update([
                'status' => $newStatus,
                'actioned_by' => $actor->id,
                'actioned_at' => now(),
                'notes' => $notes ?? $status->notes,
            ]);

            CandidateStageHistory::create([
                'candidate_stage_status_id' => $status->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => $actor->id,
                'notes' => $notes,
            ]);

            // Sync main application status
            if ($newStatus === 'rejected' || $newStatus === 'failed') {
                $status->application->update(['status' => 'rejected']);
            } elseif ($newStatus === 'pending' || $newStatus === 'in_progress') {
                $status->application->update(['status' => 'pending']);
            }

            return $status->fresh();
        });
    }
}
