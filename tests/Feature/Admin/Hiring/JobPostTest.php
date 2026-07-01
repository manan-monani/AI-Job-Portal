<?php

namespace Tests\Feature\Admin\Hiring;

use App\Enums\UserType;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['type' => UserType::SUPER_ADMIN]);
});

test('admin can view jobs index', function () {
    JobPost::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.jobs.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Hiring/Jobs/Index')
        ->has('jobs.data', 3)
    );
});

test('admin can create a job post with tags', function () {
    $department = Department::factory()->create();
    $tags = Tag::factory()->count(2)->create();

    $response = $this->actingAs($this->admin)
        ->post(route('admin.jobs.store'), [
            'department_id' => $department->id,
            'title' => 'Software Architect',
            'description' => 'Designing complex systems',
            'salary_type' => 'negotiable',
            'salary_period' => 'monthly',
            'min_experience' => 5,
            'max_experience' => 10,
            'job_type' => 'remote',
            'deadline' => now()->addMonth()->toDateString(),
            'employment_type' => 'full-time',
            'status' => 'published',
            'tags' => $tags->pluck('id')->toArray(),
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('job_posts', ['title' => 'Software Architect']);

    $job = JobPost::where('title', 'Software Architect')->first();
    expect($job->tags)->toHaveCount(2);
});

test('admin can update a job post', function () {
    $job = JobPost::factory()->create(['title' => 'Old Title']);
    $newDepartment = Department::factory()->create();

    $response = $this->actingAs($this->admin)
        ->put(route('admin.jobs.update', $job), [
            'department_id' => $newDepartment->id,
            'title' => 'New Title',
            'description' => 'Updated content',
            'salary_type' => 'negotiable',
            'salary_period' => 'yearly',
            'min_experience' => 2,
            'max_experience' => 5,
            'job_type' => 'onsite',
            'location' => 'New York',
            'deadline' => now()->addWeek()->toDateString(),
            'employment_type' => 'full-time',
            'status' => 'published',
            'tags' => [],
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('job_posts', ['id' => $job->id, 'title' => 'New Title', 'location' => 'New York']);
});

test('admin can update job status', function () {
    $job = JobPost::factory()->create(['status' => 'draft']);

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.jobs.status', $job), [
            'status' => 'published',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('job_posts', ['id' => $job->id, 'status' => 'published']);
});

test('admin can delete a job post', function () {
    $job = JobPost::factory()->create();

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.jobs.destroy', $job));

    $response->assertRedirect();
    $this->assertSoftDeleted('job_posts', ['id' => $job->id]);
});
