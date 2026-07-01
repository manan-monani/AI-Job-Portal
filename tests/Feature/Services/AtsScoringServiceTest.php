<?php

use App\Models\ApplicationResumeParse;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\SortingCriterion;
use App\Services\AtsScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it scores application correctly based on skill criterion', function () {
    $service = new AtsScoringService;

    $job = \App\Models\JobPost::factory()->create();
    $stage = PipelineStage::factory()->create([
        'job_post_id' => $job->id,
        'type' => 'sorting',
        'config' => ['passing_marks' => 10],
    ]);

    SortingCriterion::factory()->create([
        'pipeline_stage_id' => $stage->id,
        'type' => 'skill',
        'value' => 'laravel',
        'weight' => 10,
        'is_required' => true,
    ]);

    $application = JobApplication::factory()->create(['job_post_id' => $job->id]);

    ApplicationResumeParse::factory()->create([
        'job_application_id' => $application->id,
        'status' => 'done',
        'parsed_json' => ['skills' => ['laravel', 'vue', 'php']],
    ]);

    $service->scoreApplication($application, $stage);

    $application->refresh();
    expect((float) $application->ats_score_cached)->toBe(10.0);
    expect($application->ats_passed_cached)->toBeTrue();
});

test('it fails application if required criterion is missing', function () {
    $service = new AtsScoringService;

    $job = \App\Models\JobPost::factory()->create();
    $stage = PipelineStage::factory()->create([
        'job_post_id' => $job->id,
        'type' => 'sorting',
    ]);

    SortingCriterion::factory()->create([
        'pipeline_stage_id' => $stage->id,
        'type' => 'skill',
        'value' => 'python',
        'weight' => 10,
        'is_required' => true,
    ]);

    $application = JobApplication::factory()->create(['job_post_id' => $job->id]);

    ApplicationResumeParse::factory()->create([
        'job_application_id' => $application->id,
        'status' => 'done',
        'parsed_json' => ['skills' => ['laravel', 'php']],
    ]);

    $service->scoreApplication($application, $stage);

    $application->refresh();
    expect((bool) $application->ats_passed_cached)->toBeFalse();
});

test('example', function () {
    expect(true)->toBeTrue();
});
