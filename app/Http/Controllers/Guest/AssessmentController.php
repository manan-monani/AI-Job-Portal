<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\StageSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssessmentController extends Controller
{
    /**
     * Reusable validation for candidate eligibility.
     */
    /**
     * Reusable validation for candidate eligibility.
     */
    protected function checkEligibility(Request $request, JobApplication $application, PipelineStage $stage)
    {
        if (! $request->hasValidSignature()) {
            return ['status' => 'invalid_signature', 'message' => 'This assessment link is invalid or has expired.'];
        }

        // Verify the application belongs to the stage's job post
        if ($application->job_post_id !== $stage->job_post_id) {
            return ['status' => 'invalid_job', 'message' => 'Invalid job application for this stage.'];
        }

        // For POST requests (starting/submitting), we need the candidate to be actively in this stage.
        // For GET requests (viewing instructions), we can be more lenient to allow previews and re-views.
        if ($request->isMethod('POST')) {
            $isActiveInStage = $application->stageStatuses()
                ->where('pipeline_stage_id', $stage->id)
                ->where('status', 'in_progress')
                ->exists();

            if (! $isActiveInStage) {
                return ['status' => 'not_eligible', 'message' => 'You are no longer in the active evaluation stage for this assessment.'];
            }
        }

        return ['status' => 'success'];
    }

    /**
     * Show the Task / Exam submission form.
     */
    public function showTaskExam(Request $request, JobApplication $application, PipelineStage $stage)
    {
        $eligibility = $this->checkEligibility($request, $application, $stage);
        if ($eligibility['status'] !== 'success') {
            return Inertia::render('Guest/Assessment/NotEligible', [
                'message' => $eligibility['message'],
                'jobTitle' => $application->jobPost->title ?? 'N/A',
            ]);
        }

        $submission = StageSubmission::where('job_application_id', $application->id)
            ->where('pipeline_stage_id', $stage->id)
            ->first();

        // One-time participation rule (if already submitted/evaluated)
        if ($submission && in_array($submission->status, ['submitted', 'evaluated'])) {
            return Inertia::render('Guest/Assessment/AlreadyParticipated', [
                'stage' => $stage->only('id', 'title', 'type', 'subtype'),
                'jobTitle' => $application->jobPost->title,
            ]);
        }

        return Inertia::render('Guest/Assessment/TaskExam', [
            'application' => $application->only('id', 'name'),
            'stage' => $stage->only('id', 'title', 'type', 'subtype', 'instructions'),
            'job' => $application->jobPost->only('id', 'title', 'company_name'),
            'hasSubmitted' => $submission && in_array($submission->status, ['submitted', 'evaluated']),
        ]);
    }

    /**
     * Store the Task / Exam submission.
     */
    public function storeTaskExam(Request $request, JobApplication $application, PipelineStage $stage)
    {
        $request->validate([
            'email' => 'required|email',
            'file' => 'nullable|file|max:5120', // 5MB limit
            'description' => 'required|string|max:5000',
        ]);

        $eligibility = $this->checkEligibility($request, $application, $stage);
        if ($eligibility['status'] !== 'success') {
            return back()->with('error', $eligibility['message']);
        }

        // Explicit email validation for security
        if (strtolower($request->email) !== strtolower($application->email)) {
            return back()->with('error', 'The provided email does not match our records for this application.');
        }

        $submission = StageSubmission::where('job_application_id', $application->id)
            ->where('pipeline_stage_id', $stage->id)
            ->first();

        if ($submission && in_array($submission->status, ['submitted', 'evaluated'])) {
            return back()->with('error', 'You have already submitted this assessment. Retake is not allowed.');
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('assessments', 'public');
        }

        StageSubmission::updateOrCreate(
            [
                'job_application_id' => $application->id,
                'pipeline_stage_id' => $stage->id,
            ],
            [
                'status' => 'submitted',
                'file_path' => $filePath,
                'text_answer' => $request->description,
                'submitted_at' => now(),
                'started_at' => $submission ? $submission->started_at : now(),
            ]
        );

        return back()->with('success', 'Submission successful.');
    }

    /**
     * Show the Quiz Entry Page.
     */
    public function showQuizEntry(Request $request, JobApplication $application, PipelineStage $stage)
    {
        $eligibility = $this->checkEligibility($request, $application, $stage);
        if ($eligibility['status'] !== 'success') {
            return Inertia::render('Guest/Assessment/NotEligible', [
                'message' => $eligibility['message'],
                'jobTitle' => $application->jobPost->title ?? 'N/A',
            ]);
        }

        $submission = StageSubmission::where('job_application_id', $application->id)
            ->where('pipeline_stage_id', $stage->id)
            ->first();

        return Inertia::render('Guest/Assessment/QuizEntry', [
            'application' => $application->only('id', 'name'),
            'stage' => $stage->only('id', 'title', 'instructions'),
            'quizConfig' => $stage->config ?? [],
            'questionCount' => $stage->quizQuestions()->count(),
            'job' => $application->jobPost->only('id', 'title'),
            'status' => $submission ? $submission->status : null,
            'hasSubmitted' => $submission && in_array($submission->status, ['submitted', 'evaluated']),
            'startUrl' => route('assessments.quiz.start', [
                'application' => $application->id,
                'stage' => $stage->id,
                'signature' => $request->query('signature'),
            ]),
        ]);
    }

    /**
     * Handle Quiz Start (POST) - marks as started and redirects to board.
     */
    public function startQuiz(Request $request, JobApplication $application, PipelineStage $stage)
    {
        $request->validate(['email' => 'required|email']);

        $eligibility = $this->checkEligibility($request, $application, $stage);
        if ($eligibility['status'] !== 'success') {
            return back()->with('error', $eligibility['message']);
        }

        // Explicit email validation for security
        if (strtolower($request->email) !== strtolower($application->email)) {
            return back()->with('error', 'The provided email does not match our records for this application.');
        }

        $submission = StageSubmission::firstOrCreate(
            [
                'job_application_id' => $application->id,
                'pipeline_stage_id' => $stage->id,
            ],
            [
                'status' => 'started',
                'started_at' => now(),
            ]
        );

        if (in_array($submission->status, ['submitted', 'evaluated'])) {
            return back()->with('error', 'Assessment already submitted. Retake is not allowed.');
        }

        return redirect()->signedRoute('assessments.quiz.board', [
            'application' => $application->id,
            'stage' => $stage->id,
        ]);
    }

    /**
     * Show the active Quiz Board interface.
     */
    public function showQuizBoard(Request $request, JobApplication $application, PipelineStage $stage)
    {
        $eligibility = $this->checkEligibility($request, $application, $stage);
        if ($eligibility['status'] !== 'success') {
            return Inertia::render('Guest/Assessment/NotEligible', [
                'message' => $eligibility['message'],
                'jobTitle' => $application->jobPost->title ?? 'N/A',
            ]);
        }

        $submission = StageSubmission::where('job_application_id', $application->id)
            ->where('pipeline_stage_id', $stage->id)
            ->first();

        // If they bypass the entry page
        if (! $submission || $submission->status !== 'started') {
            return redirect()->signedRoute('assessments.quiz.entry', [
                'application' => $application->id,
                'stage' => $stage->id,
            ])->with('error', 'You must start the quiz properly.');
        }

        $stage->load('quizQuestions.options');

        $questions = $stage->quizQuestions->map(function ($q) {
            return [
                'id' => $q->id,
                'question' => $q->question,
                'type' => $q->type,
                'options' => $q->options->map->only('id', 'option_text'), // Exclude is_correct for public
            ];
        });

        // Time calculations
        $durationMinutes = $stage->config['duration'] ?? 30;
        $endTime = $submission->started_at->addMinutes((int) $durationMinutes);
        $timeRemainingMs = max(0, $endTime->getTimestampMs() - now()->getTimestampMs());

        // If time strictly over, force auto-submit internally? The frontend will handle it mostly, but good to check.

        return Inertia::render('Guest/Assessment/QuizBoard', [
            'application' => $application->only('id', 'name'),
            'stage' => $stage->only('id', 'title'),
            'questions' => $questions,
            'timeRemainingMs' => $timeRemainingMs,
            'submitUrl' => route('assessments.quiz.submit', [
                'application' => $application->id,
                'stage' => $stage->id,
                'signature' => $request->query('signature'),
            ]),
        ]);
    }

    /**
     * Submit and auto-grade the quiz.
     */
    public function storeQuiz(Request $request, JobApplication $application, PipelineStage $stage)
    {
        $eligibility = $this->checkEligibility($request, $application, $stage);
        if ($eligibility['status'] !== 'success') {
            return back()->with('error', $eligibility['message']);
        }

        $submission = StageSubmission::where('job_application_id', $application->id)
            ->where('pipeline_stage_id', $stage->id)
            ->first();

        if (! $submission || in_array($submission->status, ['submitted', 'evaluated'])) {
            return redirect()->signedRoute('assessments.quiz.entry', [
                'application' => $application->id,
                'stage' => $stage->id,
            ])->with('error', 'Assessment already submitted or invalid state.');
        }

        $answers = $request->input('answers', []); // Format: [question_id => [option_id, option_id]]

        $stage->load('quizQuestions.options');
        $totalMarks = 0;
        $obtainedMarks = 0;
        $resultsPayload = [];

        foreach ($stage->quizQuestions as $question) {
            $questionMark = $question->marks ?? 1;
            $totalMarks += $questionMark;

            $givenAnswerIds = collect($answers[$question->id] ?? [])->map(fn ($id) => (int) $id)->toArray();
            $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();

            // Simple grading: must match exactly all correct options
            sort($givenAnswerIds);
            sort($correctOptions);

            $isCorrect = ($givenAnswerIds === $correctOptions);
            if ($isCorrect) {
                $obtainedMarks += $questionMark;
            }

            $resultsPayload[] = [
                'question_id' => $question->id,
                'question_text' => $question->question,
                'given_answers' => $givenAnswerIds,
                'correct_answers' => $correctOptions,
                'is_correct' => $isCorrect,
                'marks_awarded' => $isCorrect ? $questionMark : 0,
            ];
        }

        $submission->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'quiz_results' => $resultsPayload,
            'obtained_mark' => $obtainedMarks,
            'total_mark' => $totalMarks,
        ]);

        return redirect()->signedRoute('assessments.quiz.entry', [
            'application' => $application->id,
            'stage' => $stage->id,
        ])->with('success', 'Quiz completed successfully.');
    }
}
