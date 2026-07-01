<?php

namespace Tests\Feature\Admin\Hiring;

use App\Enums\UserType;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['type' => UserType::SUPER_ADMIN]);
});

test('admin can view evaluations index', function () {
    $department = \App\Models\Department::factory()->create();
    $jobs = JobPost::factory()->count(3)->create([
        'department_id' => $department->id,
        'status' => 'published',
        'pipeline_enabled' => true,
    ]);

    foreach ($jobs as $job) {
        \App\Models\PipelineStage::factory()->create([
            'job_post_id' => $job->id,
            'is_enabled' => true,
        ]);
        \App\Models\JobApplication::factory()->create([
            'job_post_id' => $job->id,
        ]);
    }

    $response = $this->actingAs($this->admin)
        ->get(route('admin.evaluations.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Hiring/Evaluation/Index')
        ->has('jobs.data', 3)
    );
});
