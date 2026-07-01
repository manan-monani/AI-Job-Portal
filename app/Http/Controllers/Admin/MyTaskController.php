<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use Illuminate\Http\Request;

class MyTaskController extends Controller
{
    public function index(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::MY_TASKS);

        $user = $request->user();

        // Fetch jobs where the user is assigned as an interviewer in at least one stage
        $jobs = JobPost::query()
            ->whereHas('pipelineStages.interviewers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->with([
                'department',
                'pipelineStages' => function ($q) {
                    $q->orderBy('sort_order');
                },
                'pipelineStages.interviewers',
                // Eager load candidate statuses to determine if there are active candidates in the stages
                'pipelineStages.candidateStatuses' => function ($q) {
                    // Consider pending and in_progress as active running statuses
                    $q->whereIn('status', ['pending', 'in_progress', 'submitted', 'evaluating']);
                },
            ])
            ->latest()
            ->paginate(15);

        $mappedJobs = $jobs->through(function ($job) use ($user) {
            $totalPhases = $job->pipelineStages->count();

            // Filter out the phases the current user is assigned to
            $myPhases = $job->pipelineStages->filter(function ($stage) use ($user) {
                return $stage->interviewers->contains('id', $user->id);
            });

            // Is my phase running? -> True if any of my assigned phases have active candidates
            $isRunning = $myPhases->some(function ($stage) {
                return $stage->candidateStatuses->count() > 0;
            });

            // Identify the last opened phase (typically the highest sort order active phase)
            // Or just use the last phase in the user's assigned list that has candidates
            $activeAssignedPhases = $myPhases->filter(function ($stage) {
                return $stage->candidateStatuses->count() > 0;
            });
            $lastOpenedPhase = $activeAssignedPhases->last() ?? $myPhases->last();

            return [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'department' => $job->department?->name,
                'job_status' => $job->status,
                'hiring_status' => $job->hiring_status,
                'total_phases' => $totalPhases,
                'assigned_phases' => $myPhases->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'title' => $s->title,
                        'type' => $s->type,
                    ];
                })->values(),
                'assigned_phase_count' => $myPhases->count(),
                'is_running' => $isRunning,
                'last_opened_phase' => $lastOpenedPhase?->title ?? 'N/A',
            ];
        });

        $summary = [
            'total_jobs' => $jobs->total(),
            'active_tasks' => collect($mappedJobs->items())->where('is_running', true)->count(),
            'total_assigned_phases' => collect($mappedJobs->items())->sum('assigned_phase_count'),
        ];

        return inertia('Admin/MyTasks/Index', [
            'jobs' => $mappedJobs,
            'summary' => $summary,
        ]);
    }
}
