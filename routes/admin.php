<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'storeLogin']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'storeRegister']);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class);
    Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');

    Route::get('/my-tasks', [App\Http\Controllers\Admin\MyTaskController::class, 'index'])->name('my_tasks.index');
    Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');

    Route::get('/branding', [BrandingController::class, 'index'])->name('branding.index');
    Route::put('/branding/{brand}', [BrandingController::class, 'update'])->name('branding.update');

    Route::resource('roles', RoleController::class);

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity_logs.show');

    Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    Route::get('/business/branding', [App\Http\Controllers\Admin\BusinessSettingController::class, 'index'])->name('business.branding');
    Route::post('/business/branding', [App\Http\Controllers\Admin\BusinessSettingController::class, 'update'])->name('business.update');

    Route::get('/business/email-setup', [\App\Http\Controllers\Admin\EmailSettingController::class, 'index'])->name('business.email.index');
    Route::post('/business/email-setup', [\App\Http\Controllers\Admin\EmailSettingController::class, 'update'])->name('business.email.update');

    Route::get('/business/legal', [App\Http\Controllers\Admin\LegalController::class, 'index'])->name('legal.index');
    Route::post('/business/legal', [App\Http\Controllers\Admin\LegalController::class, 'update'])->name('legal.update');

    Route::get('/business/settings', [App\Http\Controllers\Admin\BusinessLogicController::class, 'index'])->name('business.settings.index');
    Route::post('/business/settings', [App\Http\Controllers\Admin\BusinessLogicController::class, 'update'])->name('business.settings.update');

    // Hiring
    Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);
    Route::patch('departments/{department}/status', [App\Http\Controllers\Admin\DepartmentController::class, 'updateStatus'])->name('departments.status');

    Route::resource('tags', App\Http\Controllers\Admin\TagController::class);
    Route::patch('tags/{tag}/status', [App\Http\Controllers\Admin\TagController::class, 'updateStatus'])->name('tags.status');

    Route::resource('jobs', App\Http\Controllers\Admin\JobPostController::class);
    Route::post('jobs/{job}/duplicate', [App\Http\Controllers\Admin\JobPostController::class, 'duplicate'])->name('jobs.duplicate');
    Route::patch('jobs/{job}/status', [App\Http\Controllers\Admin\JobPostController::class, 'updateStatus'])->name('jobs.status');

    // Pipeline
    Route::get('jobs/{job}/pipeline', [App\Http\Controllers\Admin\PipelineController::class, 'show'])->name('jobs.pipeline');
    Route::put('jobs/{job}/pipeline', [App\Http\Controllers\Admin\PipelineController::class, 'update'])->name('jobs.pipeline.update');
    Route::patch('jobs/{job}/pipeline-toggle', [App\Http\Controllers\Admin\PipelineController::class, 'togglePipeline'])->name('jobs.pipeline.toggle');
    Route::get('stages/{stage}/email-preview', [App\Http\Controllers\Admin\PipelineController::class, 'emailPreview'])->name('stages.email_preview');
    Route::put('stages/{stage}/email-template', [App\Http\Controllers\Admin\PipelineController::class, 'saveEmailTemplate'])->name('stages.email_template.update');
    Route::patch('candidate-stages/{candidateStageStatus}/status', [App\Http\Controllers\Admin\PipelineController::class, 'updateCandidateStatus'])->name('candidate_stages.status');

    // Evaluations
    Route::get('evaluations', [App\Http\Controllers\Admin\EvaluationController::class, 'index'])->name('evaluations.index');
    Route::get('evaluations/{job}', [App\Http\Controllers\Admin\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::post('evaluations/{job}/move', [App\Http\Controllers\Admin\EvaluationController::class, 'moveCandidates'])->name('evaluations.move');
    Route::post('evaluations/{job}/override', [App\Http\Controllers\Admin\EvaluationController::class, 'overrideAts'])->name('evaluations.override');
    Route::post('evaluations/{job}/stages/{stage}/run-ats', [App\Http\Controllers\Admin\EvaluationController::class, 'runStageAts'])->name('evaluations.run_ats');
    Route::post('evaluations/{job}/stages/{stage}/stop-ats', [App\Http\Controllers\Admin\EvaluationController::class, 'stopStageAts'])->name('evaluations.stop_ats');
    Route::post('evaluations/{job}/hiring-status', [App\Http\Controllers\Admin\EvaluationController::class, 'updateHiringStatus'])->name('evaluations.hiring_status');
    Route::put('evaluations/candidates/{application}/status', [App\Http\Controllers\Admin\EvaluationController::class, 'updateCandidateStatus'])->name('evaluations.candidates.status');
    Route::post('evaluations/stage-submissions/{submission}/grade', [App\Http\Controllers\Admin\EvaluationController::class, 'gradeSubmission'])->name('evaluations.submissions.grade');
    Route::post('evaluations/stage-submissions/{submission}/reset', [App\Http\Controllers\Admin\EvaluationController::class, 'resetSubmission'])->name('evaluations.submissions.reset');

    // Candidates
    Route::get('candidates', [App\Http\Controllers\Admin\CandidateController::class, 'index'])->name('candidates.index');
    Route::get('candidates/{jobApplication}', [App\Http\Controllers\Admin\CandidateController::class, 'show'])->name('candidates.show');
});
