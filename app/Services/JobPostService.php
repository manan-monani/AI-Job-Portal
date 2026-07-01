<?php

namespace App\Services;

use App\Models\JobPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class JobPostService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = JobPost::query()->with(['department', 'tags'])->withCount('pipelineStages');

        if (! empty($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    public function create(array $data): JobPost
    {
        return DB::transaction(function () use ($data) {
            $tags = $data['tags'] ?? [];
            unset($data['tags']);

            $jobPost = JobPost::create($data);
            $jobPost->tags()->sync($tags);

            // Auto-seed default pipeline stages
            app(\App\Services\PipelineService::class)->seedDefaultStages($jobPost);

            return $jobPost;
        });
    }

    public function update(JobPost $jobPost, array $data): bool
    {
        return DB::transaction(function () use ($jobPost, $data) {
            $tags = $data['tags'] ?? [];
            unset($data['tags']);

            $jobPost->update($data);
            $jobPost->tags()->sync($tags);

            return true;
        });
    }

    public function updateStatus(JobPost $jobPost, string $status): bool
    {
        return $jobPost->update(['status' => $status]);
    }

    public function delete(JobPost $jobPost): ?bool
    {
        return $jobPost->delete();
    }
}
