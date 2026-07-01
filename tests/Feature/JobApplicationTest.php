<?php

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('guest can apply for a job', function () {
    Storage::fake('public');
    $job = JobPost::factory()->create(['status' => 'published']);

    $file = UploadedFile::fake()->create('resume.pdf', 1000, 'application/pdf');

    $response = $this->post(route('careers.apply', $job->slug), [
        'name' => 'Guest User',
        'email' => 'guest@example.com',
        'phone' => '1234567890',
        'cv' => $file,
        'message' => 'Test application',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('job_applications', [
        'email' => 'guest@example.com',
        'job_post_id' => $job->id,
    ]);
});

test('authenticated candidate can apply for a job', function () {
    Storage::fake('public');
    $job = JobPost::factory()->create(['status' => 'published']);
    $user = User::factory()->create();
    $user->customerProfile()->create(['phone' => '0987654321']);

    $file = UploadedFile::fake()->create('resume.pdf', 1000, 'application/pdf');

    $response = $this->actingAs($user)->post(route('careers.apply', $job->slug), [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => '0987654321',
        'cv' => $file,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('job_applications', [
        'user_id' => $user->id,
        'job_post_id' => $job->id,
    ]);
});

test('duplicate applications are prevented via email for guests', function () {
    Storage::fake('public');
    $job = JobPost::factory()->create(['status' => 'published']);
    $file = UploadedFile::fake()->create('resume.pdf', 1000, 'application/pdf');

    $this->post(route('careers.apply', $job->slug), [
        'name' => 'Guest Duplicate',
        'email' => 'duplicate@example.com',
        'phone' => '1234567890',
        'cv' => $file,
    ]);

    $file2 = UploadedFile::fake()->create('resume2.pdf', 1000, 'application/pdf');
    $response = $this->post(route('careers.apply', $job->slug), [
        'name' => 'Guest Duplicate 2',
        'email' => 'duplicate@example.com',
        'phone' => '0987654321', // Added phone
        'cv' => $file2,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertDatabaseCount('job_applications', 1);
});
