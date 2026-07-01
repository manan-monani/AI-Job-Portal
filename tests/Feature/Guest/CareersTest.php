<?php

use App\Models\Department;
use App\Models\JobPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('careers index page renders with published jobs', function () {
    $publishedJob = JobPost::factory()->create(['status' => 'published']);
    $draftJob = JobPost::factory()->create(['status' => 'draft']);

    $response = $this->get(route('careers.index'));

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Guest/Careers/Index')
            ->has('jobs', 1)
            ->where('jobs.0.title', $publishedJob->title)
        );
});

test('careers index filters jobs by department', function () {
    $dept1 = Department::factory()->create();
    $dept2 = Department::factory()->create();

    $job1 = JobPost::factory()->create(['department_id' => $dept1->id, 'status' => 'published']);
    $job2 = JobPost::factory()->create(['department_id' => $dept2->id, 'status' => 'published']);

    $response = $this->get(route('careers.index', ['department_id' => $dept1->id]));

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->has('jobs', 1)
            ->where('jobs.0.id', $job1->id)
        );
});

test('job details page renders correctly', function () {
    $job = JobPost::factory()->create(['status' => 'published']);

    $response = $this->get(route('careers.show', $job->slug));

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Guest/Careers/Show')
            ->where('job.id', $job->id)
            ->where('job.title', $job->title)
        );
});

test('job details page returns 404 for non-published jobs', function () {
    $job = JobPost::factory()->create(['status' => 'draft']);

    $response = $this->get(route('careers.show', $job->slug));

    $response->assertStatus(404);
});

test('user can apply for a job with a CV', function () {
    Storage::fake('public');
    $job = JobPost::factory()->create(['status' => 'published', 'deadline' => now()->addDay()]);

    $response = $this->post(route('careers.apply', $job->slug), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '1234567890',
        'message' => 'I am a great fit!',
        'cv' => UploadedFile::fake()->create('resume.pdf', 100),
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('job_applications', [
        'job_post_id' => $job->id,
        'email' => 'john@example.com',
    ]);

    // Check file existence in fake storage
    $application = \App\Models\JobApplication::where('email', 'john@example.com')->first();
    Storage::disk('public')->assertExists($application->cv_path);
});

test('cannot apply for job after deadline', function () {
    $job = JobPost::factory()->create(['status' => 'published', 'deadline' => now()->subDay()]);

    $response = $this->post(route('careers.apply', $job->slug), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'cv' => UploadedFile::fake()->create('resume.pdf', 100),
    ]);

    $response->assertRedirect()
        ->assertSessionHas('error', 'The deadline for this job has passed.');
});
