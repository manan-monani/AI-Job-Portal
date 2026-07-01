<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJobPostRequest;
use App\Http\Requests\Admin\UpdateJobPostRequest;
use App\Http\Requests\Admin\UpdateJobStatusRequest;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\Tag;
use App\Services\JobPostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class JobPostController extends Controller
{
    public function __construct(protected JobPostService $jobPostService) {}

    public function index(): Response
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        return Inertia::render('Admin/Hiring/Jobs/Index', [
            'jobs' => $this->jobPostService->getAll(request()->only(['search', 'department_id', 'status'])),
            'departments' => Department::where('status', true)->get(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        return Inertia::render('Admin/Hiring/Jobs/Create', [
            'departments' => Department::where('status', true)->get(),
            'tags' => Tag::where('status', true)->get(),
        ]);
    }

    public function store(StoreJobPostRequest $request): RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $this->jobPostService->create($request->validated());

        return to_route('admin.jobs.index')->with('success', 'Job post created successfully.');
    }

    public function edit(JobPost $job): Response
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $job->load(['tags', 'department']);

        return Inertia::render('Admin/Hiring/Jobs/Edit', [
            'job' => $job,
            'departments' => Department::where('status', true)->get(),
            'tags' => Tag::where('status', true)->get(),
        ]);
    }

    public function update(UpdateJobPostRequest $request, JobPost $job): RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $this->jobPostService->update($job, $request->validated());

        return to_route('admin.jobs.index')->with('success', 'Job post updated successfully.');
    }

    public function updateStatus(UpdateJobStatusRequest $request, JobPost $job): RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $this->jobPostService->updateStatus($job, $request->validated()['status']);

        return back()->with('success', 'Job status updated successfully.');
    }

    public function duplicate(JobPost $job): RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $job->load('tags');
        $tagIds = $job->tags->pluck('id')->toArray();

        $data = $job->toArray();
        $data['title'] = 'Copy '.$data['title'];
        $data['deadline'] = now()->toDateString();
        $data['status'] = 'draft';
        $data['tags'] = $tagIds;

        $this->jobPostService->create($data);

        return back()->with('success', 'Job duplicated successfully as draft.');
    }

    public function destroy(JobPost $job): RedirectResponse
    {
        Gate::authorize(Permissions::JOBS_MANAGE);

        $this->jobPostService->delete($job);

        return back()->with('success', 'Job post deleted successfully.');
    }
}
