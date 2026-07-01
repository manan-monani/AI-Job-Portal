<?php

use App\Models\ActivityLog;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin dashboard returns expected stats and props', function () {
    $admin = User::factory()->create(['type' => 'admin']);

    // Seed some data
    $activeJob = JobPost::factory()->create(['status' => 'published', 'created_at' => now()]);
    $draftJob = JobPost::factory()->create(['status' => 'draft']);

    $application = JobApplication::factory()->create([
        'job_post_id' => $activeJob->id,
        'status' => 'pending',
        'created_at' => now()->subDays(10), // For stalled
        'updated_at' => now()->subDays(10),
    ]);

    $interview = Interview::create([
        'job_application_id' => $application->id,
        'interviewer_id' => $admin->id,
        'scheduled_at' => now()->addDays(2),
        'status' => 'scheduled',
    ]);

    ActivityLog::create([
        'user_id' => $admin->id,
        'event' => 'created',
        'description' => 'Test activity',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Pages/Dashboard')
            ->has('stats.jobs')
            ->has('stats.applications')
            ->where('stats.jobs.total_active', 1)
            ->where('stats.stalled_candidates_count', 1)
            ->has('top_jobs')
            ->has('upcoming_interviews')
            ->has('recent_activity')
            ->where('recent_activity.0.description', 'Test activity')
        );
});

test('guest cannot access admin dashboard', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('login'));
});
