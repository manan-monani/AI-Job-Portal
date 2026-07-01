<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\JobApplication;
use App\Models\JobPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CandidateController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize(Permissions::CANDIDATES_VIEW);

        $query = JobApplication::query()
            ->with(['jobPost.department', 'stageStatuses.stage'])
            ->latest();

        // Filters
        if ($request->filled('job_post_id')) {
            $query->where('job_post_id', $request->job_post_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('jobPost', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $candidates = $query->paginate(10)->withQueryString();

        // Compute dynamic evaluation status from pipeline progress
        $candidates->getCollection()->transform(function ($application) {
            $application->evaluation_status = $this->computeEvaluationStatus($application);

            return $application;
        });

        return Inertia::render('Admin/Hiring/Candidates/Index', [
            'candidates' => $candidates,
            'filters' => $request->only(['job_post_id', 'department_id']),
            'jobs' => JobPost::select('id', 'title')->get(),
            'departments' => Department::select('id', 'name')->get(),
        ]);
    }

    private function computeEvaluationStatus(JobApplication $application): string
    {
        // If explicitly hired or rejected, use that
        if ($application->status === 'hired') {
            return 'hired';
        }
        if ($application->status === 'rejected') {
            return 'rejected';
        }

        // Otherwise, compute from pipeline stage progress
        $stageStatuses = $application->stageStatuses;
        if ($stageStatuses->isEmpty()) {
            return 'pending';
        }

        // Find the highest stage the candidate has reached
        $currentStage = $stageStatuses
            ->sortByDesc(fn ($s) => $s->stage?->sort_order ?? 0)
            ->first();

        if (! $currentStage || ! $currentStage->stage) {
            return 'pending';
        }

        // If they're on the onboard stage, they're hired
        if ($currentStage->stage->system_key === 'onboard_mail') {
            return 'hired';
        }

        // If they're evaluating somewhere in the pipeline
        return 'evaluating';
    }

    public function show(JobApplication $jobApplication): JsonResponse
    {
        Gate::authorize(Permissions::CANDIDATES_VIEW);

        $jobApplication->load([
            'jobPost.department',
            'resume',
            'stageStatuses' => function ($query) {
                $query->with(['stage'])->latest();
            },
        ]);

        return response()->json([
            'application' => $jobApplication,
        ]);
    }
}
