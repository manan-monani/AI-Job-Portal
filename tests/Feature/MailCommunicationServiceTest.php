<?php

use App\Models\BusinessSetting;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\MailLog;
use App\Models\PipelineStage;
use App\Models\StageEmailTemplate;
use App\Services\MailCommunicationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    // Seed mail settings
    BusinessSetting::updateOrCreate(['key' => 'mail_enabled'], ['value' => '1']);
    BusinessSetting::updateOrCreate(['key' => 'business_name'], ['value' => 'TestCorp']);
    BusinessSetting::updateOrCreate(['key' => 'mail_from_address'], ['value' => 'no-reply@test.com']);
    BusinessSetting::updateOrCreate(['key' => 'mail_from_name'], ['value' => 'TestCorp HR']);

    // Clear both Laravel cache and the static cache in business_config()
    Cache::forget('business_settings_all');
});

it('skips sending when mail is disabled', function () {
    BusinessSetting::updateOrCreate(['key' => 'mail_enabled'], ['value' => '0']);
    Cache::forget('business_settings_all');

    $jobPost = JobPost::factory()->create();
    $application = JobApplication::factory()->create([
        'job_post_id' => $jobPost->id,
        'email' => 'candidate@example.com',
    ]);

    $service = app(MailCommunicationService::class);
    $result = $service->sendApplicationConfirmation($application);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toBe('Mail sending is disabled.');
});

it('sends application confirmation and creates a mail log', function () {
    Mail::fake();

    $jobPost = JobPost::factory()->create(['title' => 'Laravel Developer']);
    $application = JobApplication::factory()->create([
        'job_post_id' => $jobPost->id,
        'email' => 'candidate@example.com',
        'name' => 'Jane Doe',
    ]);

    $service = app(MailCommunicationService::class);
    $result = $service->sendApplicationConfirmation($application);

    expect($result['success'])->toBeTrue();
    expect($result['log_id'])->not->toBeNull();

    $log = MailLog::find($result['log_id']);
    expect($log->recipient_email)->toBe('candidate@example.com');
    expect($log->status)->toBeIn(['queued', 'sent']); // sync driver processes immediately
    expect($log->send_mode)->toBe('auto');
    expect($log->triggered_by)->toBe('system');
});

it('prevents duplicate sends via idempotency key', function () {
    Mail::fake();

    $jobPost = JobPost::factory()->create();
    $application = JobApplication::factory()->create([
        'job_post_id' => $jobPost->id,
        'email' => 'candidate@example.com',
    ]);

    $service = app(MailCommunicationService::class);
    $result1 = $service->sendApplicationConfirmation($application);
    $result2 = $service->sendApplicationConfirmation($application);

    expect($result1['log_id'])->not->toBeNull();
    expect($result2['message'])->toBe('Confirmation email already sent.');

    expect(MailLog::count())->toBe(1);
});

it('sends stage email when template exists', function () {
    Mail::fake();

    $jobPost = JobPost::factory()->create(['title' => 'Backend Dev']);
    $application = JobApplication::factory()->create([
        'job_post_id' => $jobPost->id,
        'email' => 'dev@example.com',
        'name' => 'Bob',
    ]);

    $stage = PipelineStage::factory()->create([
        'job_post_id' => $jobPost->id,
        'type' => 'assessment',
        'subtype' => 'task',
        'title' => 'Code Review',
    ]);

    // Delete any auto-generated template and create ours
    StageEmailTemplate::where('pipeline_stage_id', $stage->id)->delete();
    StageEmailTemplate::create([
        'pipeline_stage_id' => $stage->id,
        'subject' => '{{company_name}} — Task for {{job_title}}',
        'body' => 'Dear {{candidate_name}}, please complete {{stage_title}}.',
    ]);

    $service = app(MailCommunicationService::class);
    $result = $service->sendStageEmail($application, $stage);

    expect($result['success'])->toBeTrue();

    $log = MailLog::find($result['log_id']);
    expect($log->subject)->toContain('TestCorp');
    expect($log->subject)->toContain('Backend Dev');
});

it('sends stage email with default template when no custom template is configured', function () {
    Mail::fake();

    $jobPost = JobPost::factory()->create(['title' => 'Senior Dev']);
    $application = JobApplication::factory()->create([
        'job_post_id' => $jobPost->id,
        'email' => 'dev@example.com',
        'name' => 'Alice',
    ]);

    $stage = PipelineStage::factory()->create([
        'job_post_id' => $jobPost->id,
        'type' => 'interview',
        'title' => 'Final Interview',
    ]);

    // Ensure no template exists
    StageEmailTemplate::where('pipeline_stage_id', $stage->id)->delete();

    $service = app(MailCommunicationService::class);
    $result = $service->sendStageEmail($application, $stage);

    expect($result['success'])->toBeTrue();
    expect($result['log_id'])->not->toBeNull();

    $log = MailLog::find($result['log_id']);
    expect($log->status)->toBeIn(['queued', 'sent']);
    expect($log->subject)->toContain('Final Interview');
    expect($log->subject)->toContain('Senior Dev');
});

it('fails when recipient email is invalid', function () {
    Mail::fake();

    $jobPost = JobPost::factory()->create();
    $application = JobApplication::factory()->create([
        'job_post_id' => $jobPost->id,
        'email' => 'not-an-email',
    ]);

    $stage = PipelineStage::factory()->create([
        'job_post_id' => $jobPost->id,
    ]);

    StageEmailTemplate::where('pipeline_stage_id', $stage->id)->delete();
    StageEmailTemplate::create([
        'pipeline_stage_id' => $stage->id,
        'subject' => 'Test',
        'body' => 'Test body',
    ]);

    $service = app(MailCommunicationService::class);
    $result = $service->sendStageEmail($application, $stage);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('Recipient email');
});
