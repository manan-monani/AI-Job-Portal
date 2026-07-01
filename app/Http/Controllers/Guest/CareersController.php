<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CareersController extends Controller
{
    public function index(Request $request): Response
    {
        $jobs = JobPost::query()
            ->where('status', 'published')
            ->when($request->department_id, fn ($q) => $q->where('department_id', $request->department_id))
            ->whereHas('department', fn ($q) => $q->where('status', true))
            ->with('department')
            ->latest()
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'slug' => $job->slug,
                    'title' => $job->title,
                    'description' => \Illuminate\Support\Str::limit($job->description, 150),
                    'job_type' => $job->job_type,
                    'employment_type' => $job->employment_type,
                    'deadline' => $job->deadline,
                    'department' => $job->department?->name,
                ];
            });

        $departments = Department::whereHas('jobPosts', fn ($q) => $q->where('status', 'published'))
            ->where('status', true)
            ->get(['id', 'name']);

        return Inertia::render('Guest/Careers/Index', [
            'jobs' => $jobs,
            'departments' => $departments,
            'filters' => $request->only(['department_id']),
        ]);
    }

    public function show(string $slug): Response
    {
        $job = JobPost::where('slug', $slug)
            ->where('status', 'published')
            ->with(['department', 'tags'])
            ->firstOrFail();

        $hasApplied = false;
        if (auth()->check()) {
            $hasApplied = \App\Models\JobApplication::where('user_id', auth()->id())
                ->where('job_post_id', $job->id)
                ->exists();
        }

        return Inertia::render('Guest/Careers/Show', [
            'job' => $job,
            'has_applied' => $hasApplied,
        ]);
    }
}
