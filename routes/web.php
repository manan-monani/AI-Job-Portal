<?php

use App\Http\Controllers\Guest\AuthController;
use App\Http\Controllers\Guest\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\Guest\CareersController::class, 'index'])->name('careers.index');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'storeLogin']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'storeRegister']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
});

Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/privacy-policy', [App\Http\Controllers\Guest\LegalPageController::class, 'show'])->defaults('slug', 'privacy-policy')->name('legal.privacy');
Route::get('/terms-and-conditions', [App\Http\Controllers\Guest\LegalPageController::class, 'show'])->defaults('slug', 'terms-and-conditions')->name('legal.terms');
Route::get('/about-us', [App\Http\Controllers\Guest\LegalPageController::class, 'show'])->defaults('slug', 'about-us')->name('legal.about');
Route::get('/contact-us', [App\Http\Controllers\Guest\ContactController::class, 'show'])->name('contact');

Route::get('jobs/{slug}', [App\Http\Controllers\Guest\CareersController::class, 'show'])->name('careers.show');
Route::post('jobs/{slug}/apply', [App\Http\Controllers\Guest\JobApplicationController::class, 'store'])->name('careers.apply');

// Public Assessment Signed Routes
Route::prefix('assessments/{application}/{stage}')->name('assessments.')->middleware('signed')->group(function () {
    Route::get('/task', [App\Http\Controllers\Guest\AssessmentController::class, 'showTaskExam'])->name('task.show');
    Route::post('/task', [App\Http\Controllers\Guest\AssessmentController::class, 'storeTaskExam'])->name('task.store');

    Route::get('/quiz', [App\Http\Controllers\Guest\AssessmentController::class, 'showQuizEntry'])->name('quiz.entry');
    Route::post('/quiz', [App\Http\Controllers\Guest\AssessmentController::class, 'startQuiz'])->name('quiz.start');

    Route::get('/quiz/board', [App\Http\Controllers\Guest\AssessmentController::class, 'showQuizBoard'])->name('quiz.board');
    Route::post('/quiz/board', [App\Http\Controllers\Guest\AssessmentController::class, 'storeQuiz'])->name('quiz.submit');
});
