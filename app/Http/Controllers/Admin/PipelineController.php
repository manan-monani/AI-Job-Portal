<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePipelineRequest;
use App\Http\Requests\Admin\UpdateCandidateStatusRequest;
use App\Models\CandidateStageStatus;
use App\Models\JobPost;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\PipelineService;
use App\Services\StageEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PipelineController extends Controller
{
    public function __construct(
        protected PipelineService $pipelineService,
        protected StageEmailService $emailService,
    ) {}

    public function show(JobPost $job): Response
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        // Seed default stages if none exist
        if ($job->pipelineStages()->count() === 0) {
            $this->pipelineService->seedDefaultStages($job);
        }

        $stages = $this->pipelineService->getStagesForJob($job);

        // Get available interviewers (admin & super-admin users)
        $interviewers = User::whereIn('type', ['admin', 'super-admin'])
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Hiring/Pipeline/Builder', [
            'job' => $job->only('id', 'title', 'slug', 'status', 'pipeline_enabled'),
            'stages' => $stages,
            'interviewers' => $interviewers,
        ]);
    }

    public function update(StorePipelineRequest $request, JobPost $job): RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $this->pipelineService->syncStages($job, $request->validated()['stages']);

        return back()->with('success', 'Pipeline configuration saved successfully.');
    }

    public function togglePipeline(JobPost $job): RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $job->update(['pipeline_enabled' => ! $job->pipeline_enabled]);

        return back()->with('success', $job->pipeline_enabled ? 'Pipeline enabled.' : 'Pipeline disabled.');
    }

    public function emailPreview(PipelineStage $stage): JsonResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $stage->load(['jobPost', 'emailTemplate']);

        // Get saved template or generate default
        $template = $stage->emailTemplate;
        if (! $template) {
            $default = $this->emailService->generateDefaultTemplate($stage);
            $subject = $default['subject'];
            $body = $default['body'];
        } else {
            $subject = $template->subject;
            $body = $template->body;
        }

        // Render preview with sample data
        $sampleData = $this->emailService->getSampleData($stage);
        $rendered = $this->emailService->renderTemplate($subject, $body, $sampleData);

        return response()->json([
            'subject' => $subject,
            'body' => $body,
            'rendered_subject' => $rendered['subject'],
            'rendered_body' => $rendered['body'],
            'tokens' => $this->emailService->getAvailableTokens($stage),
            'sample_data' => $sampleData,
        ]);
    }

    public function saveEmailTemplate(Request $request, PipelineStage $stage): JsonResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
        ]);

        $template = $stage->emailTemplate()->updateOrCreate(
            ['pipeline_stage_id' => $stage->id],
            $validated,
        );

        return response()->json([
            'message' => 'Email template saved successfully.',
            'template' => $template,
        ]);
    }

    public function updateCandidateStatus(
        UpdateCandidateStatusRequest $request,
        CandidateStageStatus $candidateStageStatus
    ): RedirectResponse {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $this->pipelineService->moveCandidate(
            $candidateStageStatus,
            $request->validated()['status'],
            $request->user(),
            $request->validated()['notes'] ?? null,
        );

        return back()->with('success', 'Candidate status updated successfully.');
    }
}
