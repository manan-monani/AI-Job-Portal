<?php

use App\Services\ResumeNormalizerService;

test('it normalizes synonyms correctly', function () {
    $service = new ResumeNormalizerService;
    $data = $service->parse('I work with Nodejs and Reactjs');

    expect($data['skills'])->toContain('node.js');
    expect($data['skills'])->toContain('react');
});

test('it extracts experience years correctly', function () {
    $service = new ResumeNormalizerService;
    $data = $service->parse('Senior Developer with 5+ years of experience');

    expect($data['inferred_experience_years'])->toBe(5.0);
});

test('it extracts multiple skills correctly', function () {
    $service = new ResumeNormalizerService;
    $data = $service->parse('Skills: PHP, Laravel, MySQL, Docker');

    expect($data['skills'])->toContain('php');
    expect($data['skills'])->toContain('laravel');
    expect($data['skills'])->toContain('mysql');
    expect($data['skills'])->toContain('docker');
});

test('example', function () {
    expect(true)->toBeTrue();
});
