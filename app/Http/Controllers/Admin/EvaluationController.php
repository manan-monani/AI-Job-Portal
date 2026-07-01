<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Models\CandidateStageStatus;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EvaluationController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize(Permissions::JOBS_MANAGE); // Or a specific evaluation permission

        $jobs = JobPost::query()
            ->whereNotIn('status', ['draft', 'hidden'])
            ->where('pipeline_enabled', true)
            ->whereHas('pipelineStages')
            ->whereHas('applications')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->department_id, function ($query, $departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->with(['department'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Add hiring_status and candidate stats
        $jobs->getCollection()->transform(function ($job) {
            $job->hiring_status = $job->hiring_status;
            $job->loadCount('applications');

            // Get stages and current candidate distribution
            $stages = $job->pipelineStages()
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->get();

            // Calculate distribution: For each application, find its current stage
            $applications = $job->applications()->with(['stageStatuses.stage'])->get();
            $distribution = $stages->map(function ($stage) use ($applications) {
                $count = $applications->filter(function ($app) use ($stage) {
                    // Current stage is the one with highest sort_order among non-skipped statuses
                    $activeStatus = $app->stageStatuses
                        ->where('status', '!=', 'skipped')
                        ->sortByDesc(fn ($s) => $s->stage->sort_order)
                        ->first();

                    return $activeStatus && $activeStatus->pipeline_stage_id === $stage->id;
                })->count();

                return [
                    'id' => $stage->id,
                    'title' => $stage->title,
                    'count' => $count,
                ];
            });

            $job->stage_stats = $distribution;

            return $job;
        });

        return Inertia::render('Admin/Hiring/Evaluation/Index', [
            'jobs' => $jobs,
            'departments' => \App\Models\Department::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'department_id']),
        ]);
    }

    public function show(JobPost $job): Response
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $job->load([
            'pipelineStages' => function ($query) {
                $query->where('is_enabled', true)->orderBy('sort_order');
            },
        ]);

        $stages = $job->pipelineStages;
        if ($stages->isEmpty()) {
            return back()->with('error', 'This job has no active pipeline stages.');
        }

        // Initialize candidates in Stage 1 if they don't have a status yet
        $firstStage = $stages->first();
        $applicationsWithoutStatus = $job->applications()->whereDoesntHave('stageStatuses')->get();
        foreach ($applicationsWithoutStatus as $app) {
            CandidateStageStatus::create([
                'pipeline_stage_id' => $firstStage->id,
                'job_application_id' => $app->id,
                'status' => 'in_progress',
                'actioned_by' => auth()->id(),
                'actioned_at' => now(),
            ]);
        }

        // Get all active statuses for this job (highest sort_order stage for each application where status != skipped)
        $candidatesRaw = CandidateStageStatus::with(['application.user', 'application.resume', 'stage.criteria', 'application.atsScores', 'application.atsOverrides', 'application.resumeParses', 'application.stageSubmissions'])
            ->whereHas('stage', function ($q) use ($job) {
                $q->where('job_post_id', $job->id);
            })
            ->get();

        // Group by application to find their current active stage and completed stages
        $candidatesByApp = $candidatesRaw->groupBy('job_application_id');

        $candidates = [];
        foreach ($candidatesByApp as $appId => $statuses) {
            // Find the highest sort_order stage
            $activeStatus = $statuses->sortByDesc(fn ($s) => $s->stage->sort_order)
                ->firstWhere('status', '!=', 'skipped');

            if (! $activeStatus) {
                $activeStatus = $statuses->sortByDesc(fn ($s) => $s->stage->sort_order)->first();
            }

            // We need all completed stage IDs for rules (forward-only logic)
            $completedStageIds = $statuses->filter(function ($s) use ($activeStatus) {
                return $s->stage->sort_order < $activeStatus->stage->sort_order;
            })->pluck('pipeline_stage_id')->toArray();

            $application = $activeStatus->application;
            $isSelectedByCriteria = null;
            $atsScore = null;
            $atsBreakdown = null;

            if ($activeStatus->stage->type === 'sorting') {
                $scoreRecord = $application->atsScores->where('pipeline_stage_id', $activeStatus->stage->id)->first();
                $overrideRecord = $application->atsOverrides->where('pipeline_stage_id', $activeStatus->stage->id)->first();

                if ($scoreRecord) {
                    $isSelectedByCriteria = $scoreRecord->passed;
                    $atsScore = $scoreRecord->total_score;
                    $atsBreakdown = $scoreRecord->criteria_breakdown;
                } elseif ($application->ats_state === 'scored') {
                    // Fallback to cached fields if record is missing (e.g. no criteria)
                    $isSelectedByCriteria = $application->ats_passed_cached;
                    $atsScore = $application->ats_score_cached;
                }

                if ($overrideRecord) {
                    // Manual override takes precedence over algorithm
                    $isSelectedByCriteria = $overrideRecord->overridden_passed;
                }
            }

            $submission = $application->stageSubmissions->where('pipeline_stage_id', $activeStatus->pipeline_stage_id)->first();

            $candidates[] = [
                'id' => $application->id,
                'name' => $application->name,
                'email' => $application->email,
                'cover_letter' => $application->message,
                'resume' => $application->resume ? [
                    'url' => asset('storage/'.$application->resume->file_path),
                    'name' => $application->resume->cv_title,
                ] : null,
                'current_stage_id' => $activeStatus->pipeline_stage_id,
                'status' => $activeStatus->status, // in_progress, passed, failed
                'completed_stage_ids' => $completedStageIds,
                'is_selected_by_criteria' => $isSelectedByCriteria,
                'ats_state' => $application->ats_state,
                'ats_score' => $atsScore,
                'ats_breakdown' => $atsBreakdown,
                'stage_submission' => $submission ? [
                    'id' => $submission->id,
                    'status' => $submission->status,
                    'obtained_mark' => $submission->obtained_mark,
                    'total_mark' => $submission->total_mark,
                    'file_url' => $submission->file_path ? asset('storage/'.$submission->file_path) : null,
                    'text_answer' => $submission->text_answer,
                    'quiz_results' => $submission->quiz_results,
                ] : null,
                'assessment_url' => $activeStatus->stage->type === 'assessment' ? \Illuminate\Support\Facades\URL::signedRoute(
                    match ($activeStatus->stage->subtype) {
                        'quiz', 'online_quiz' => 'assessments.quiz.entry',
                        default => 'assessments.task.show',
                    },
                    ['application' => $application->id, 'stage' => $activeStatus->stage->id]
                ) : null,
            ];
        }

        // Sort overall by ATS score primarily if it exists, otherwise by ID
        $sortedCandidates = collect($candidates)->sortByDesc(function ($c) {
            return $c['ats_score'] ?? -1;
        })->values();

        // Assign Rank based on the sorted order within their respective stages
        $groupedCandidates = $sortedCandidates->groupBy('current_stage_id')->map(function ($stageCandidates) {
            return $stageCandidates->values()->map(function ($candidate, $index) {
                $candidate['ats_rank'] = $candidate['ats_score'] !== null ? ($index + 1) : null;

                return $candidate;
            });
        });

        return Inertia::render('Admin/Hiring/Evaluation/Show', [
            'job' => $job->load(['pipelineStages.emailTemplate', 'department']),
            'stages' => $stages->map(function ($stage) use ($job) {
                // Find a representative application to generate a preview link
                $representative = $job->applications()
                    ->whereHas('stageStatuses', fn ($q) => $q->where('pipeline_stage_id', $stage->id))
                    ->first() ?? $job->applications()->first();

                $assessmentUrl = null;
                if ($stage->type === 'assessment' && $representative) {
                    $assessmentUrl = \Illuminate\Support\Facades\URL::signedRoute(
                        match ($stage->subtype) {
                            'quiz', 'online_quiz' => 'assessments.quiz.entry',
                            default => 'assessments.task.show',
                        },
                        ['application' => $representative->id, 'stage' => $stage->id]
                    );
                }

                return array_merge(
                    $stage->only('id', 'title', 'sort_order', 'type', 'subtype', 'is_system', 'system_key', 'send_mail_on_trigger', 'config'),
                    [
                        'has_email_template' => $stage->emailTemplate()->exists(),
                        'email_template' => $stage->emailTemplate ? [
                            'subject' => $stage->emailTemplate->subject,
                            'body' => $stage->emailTemplate->body,
                        ] : null,
                        'assessment_url' => $assessmentUrl,
                    ],
                );
            }),
            'candidates' => $groupedCandidates,
            'global_rejection_email' => [
                'subject' => business_config('rejection_email_subject'),
                'body' => business_config('rejection_email_body'),
            ],
        ]);
    }

    public function moveCandidates(Request $request, JobPost $job, \App\Services\PipelineTransitionService $transitionService): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $validated = $request->validate([
            'candidate_ids' => ['required', 'array'],
            'candidate_ids.*' => ['exists:job_applications,id'],
            'from_stage_id' => ['required', 'exists:pipeline_stages,id'],
            'to_stage_id' => ['required', 'exists:pipeline_stages,id'],
            'send_mail' => ['boolean'],
        ]);

        try {
            $transitionService->moveCandidates(
                $validated['candidate_ids'],
                $validated['from_stage_id'],
                $validated['to_stage_id'],
                $request->boolean('send_mail')
            );

            return back()->with('success', 'Candidates moved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function overrideAts(Request $request, JobPost $job): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $validated = $request->validate([
            'candidate_id' => ['required', 'exists:job_applications,id'],
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
            'overridden_passed' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $application = \App\Models\JobApplication::findOrFail($validated['candidate_id']);
        $scoreRecord = $application->atsScores()->where('pipeline_stage_id', $validated['pipeline_stage_id'])->first();

        \App\Models\ApplicationAtsOverride::updateOrCreate(
            [
                'job_application_id' => $application->id,
                'pipeline_stage_id' => $validated['pipeline_stage_id'],
            ],
            [
                'overridden_by' => auth()->id(),
                'original_passed' => $scoreRecord ? $scoreRecord->passed : false,
                'overridden_passed' => $validated['overridden_passed'],
                'reason' => $validated['reason'],
            ]
        );

        // Update cache fields
        $application->update([
            'ats_passed_cached' => $validated['overridden_passed'],
        ]);

        return back()->with('success', 'Manual override applied successfully.');
    }

    public function runStageAts(JobPost $job, \App\Models\PipelineStage $stage): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        if ($stage->job_post_id !== $job->id) {
            abort(403, 'Stage does not belong to this job.');
        }

        if ($stage->type !== 'sorting') {
            return back()->with('error', 'ATS scoring can only be run for sorting stages.');
        }

        // Get all candidates currently in this stage
        $candidateIds = \App\Models\CandidateStageStatus::query()
            ->where('pipeline_stage_id', $stage->id)
            ->where('status', 'in_progress')
            ->pluck('job_application_id');

        if ($candidateIds->isEmpty()) {
            return back()->with('success', 'No candidates in this stage to scan.');
        }

        // Update state and dispatch parsing jobs (which sequentially trigger scoring)
        foreach ($candidateIds as $appId) {
            \App\Models\JobApplication::where('id', $appId)->update(['ats_state' => 'processing']);
            \App\Jobs\ParseResumeJob::dispatch($appId);
        }

        return back();
    }

    public function stopStageAts(JobPost $job, \App\Models\PipelineStage $stage): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        if ($stage->job_post_id !== $job->id) {
            abort(403, 'Stage does not belong to this job.');
        }

        // Reset all candidates in 'processing' or 'pending' state in this stage to 'not_started'
        $candidateIds = \App\Models\CandidateStageStatus::query()
            ->where('pipeline_stage_id', $stage->id)
            ->pluck('job_application_id');

        \App\Models\JobApplication::whereIn('id', $candidateIds)
            ->whereIn('ats_state', ['processing', 'pending'])
            ->update(['ats_state' => 'not_started']);

        return back()->with('success', 'ATS scanning stopped and reset for this stage.');
    }

    public function updateHiringStatus(Request $request, JobPost $job): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $validated = $request->validate([
            'hiring_status' => ['required', 'string', 'in:in_progress,hired_closed,not_hired_closed,continue_hiring'],
        ]);

        $hiringStatus = $validated['hiring_status'];
        $updates = ['hiring_status' => $hiringStatus];

        // Close the job when hired or not hired
        if (in_array($hiringStatus, ['hired_closed', 'not_hired_closed'])) {
            $updates['status'] = 'hidden';
        }

        // Re-open the job when continuing hiring
        if ($hiringStatus === 'continue_hiring') {
            $updates['status'] = 'published';
            $updates['hiring_status'] = 'in_progress';
        }

        $job->update($updates);

        $label = match ($hiringStatus) {
            'hired_closed' => 'Hired & Closed',
            'not_hired_closed' => 'Not Hired & Closed',
            'continue_hiring' => 'Continue Hiring',
            default => 'In Progress',
        };

        return back()->with('success', "Job status updated to: {$label}");
    }

    public function gradeSubmission(Request $request, \App\Models\StageSubmission $submission): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $submission->load('stage');
        $totalMark = $submission->stage->config['total_marks'] ?? $submission->total_mark ?? null;

        $validated = $request->validate([
            'obtained_mark' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($totalMark) {
                    if ($totalMark !== null && $value > $totalMark) {
                        $fail(__('The score cannot be greater than the total marks (:total).', ['total' => $totalMark]));
                    }
                },
            ],
        ]);

        $submission->update([
            'obtained_mark' => $validated['obtained_mark'],
            'status' => 'evaluated',
        ]);

        return back()->with('success', 'Submission evaluated successfully.');
    }

    public function resetSubmission(\App\Models\StageSubmission $submission): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $submission->delete();

        return back()->with('success', 'Assessment submission reset successfully. The candidate can now re-participate.');
    }

    public function updateCandidateStatus(Request $request, \App\Models\JobApplication $application): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:in_progress,rejected'],
            'send_email' => ['boolean'],
            'email_subject' => ['required_if:send_email,true', 'nullable', 'string', 'max:255'],
            'email_body' => ['required_if:send_email,true', 'nullable', 'string'],
        ]);

        $status = $validated['status'];

        // Update the current active stage status
        $activeStatus = \App\Models\CandidateStageStatus::query()
            ->where('job_application_id', $application->id)
            ->where('status', '!=', 'skipped')
            ->orderByDesc(function ($q) {
                $q->select('sort_order')
                    ->from('pipeline_stages')
                    ->whereColumn('pipeline_stages.id', 'candidate_stage_statuses.pipeline_stage_id')
                    ->limit(1);
            })
            ->first();

        if ($activeStatus) {
            $activeStatus->update([
                'status' => $status,
                'actioned_by' => auth()->id(),
                'actioned_at' => now(),
            ]);

            // Sync main application status
            $application->update([
                'status' => $status === 'rejected' ? 'rejected' : 'pending',
            ]);

            // Handle email sending
            if ($request->boolean('send_email') && $status === 'rejected') {
                // Persist globally if changed
                app(\App\Services\BusinessSettingService::class)->updateSettings([
                    'rejection_email_subject' => $validated['email_subject'],
                    'rejection_email_body' => $validated['email_body'],
                ]);

                try {
                    app(\App\Services\MailCommunicationService::class)->sendCustomEmail(
                        recipientEmail: $application->email,
                        subject: $validated['email_subject'],
                        body: $validated['email_body'],
                        applicationId: $application->id,
                        stageId: $activeStatus->pipeline_stage_id,
                        jobPostId: $application->job_post_id,
                        adminUserId: auth()->id()
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send rejection email: '.$e->getMessage());
                }
            }
        }

        $message = $status === 'rejected' ? 'Candidate rejected successfully.' : 'Candidate status updated to Evaluating.';

        return back()->with('success', $message);
    }
}
