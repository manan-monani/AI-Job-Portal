<?php

namespace Database\Seeders;

use App\Models\JobPost;
use App\Models\PipelineStage;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;

class PipelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only run for jobs where pipeline is enabled
        $jobs = JobPost::where('pipeline_enabled', true)->get();

        $manager = User::where('email', 'manager@admin.com')->first();
        if (! $manager) {
            $this->command->error('Manager user not found! Please run UserSeeder first.');

            return;
        }

        foreach ($jobs as $job) {
            $this->seedPipelineForJob($job, $manager);
        }

        $this->command->info('Pipelines and Quizzes seeded successfully.');
    }

    private function seedPipelineForJob(JobPost $job, User $manager)
    {
        // 1. Initial System Stage (CV Received typically generated automatically but let's be explicit if needed)
        // Check if system stage exists
        if (! PipelineStage::where('job_post_id', $job->id)->where('is_system', true)->exists()) {
            // Let's rely on standard logic, if system stage missing, we skip creating it manually unless required. Standard jobs usually create it on model boot or similar. Assume we need to create stages.
        }

        // 1. Sorting Stage
        $sortingStage = PipelineStage::updateOrCreate(
            [
                'job_post_id' => $job->id,
                'title' => 'Initial Screening',
                'type' => 'sorting',
            ],
            [
                'sort_order' => 2,
                'instructions' => 'Review resumes and block candidates that lack the core requirements. Prioritize candidates with robust portfolios.',
                'is_enabled' => true,
            ]
        );
        $sortingStage->interviewers()->syncWithoutDetaching([$manager->id]);

        // 2. Assessment: Quiz
        $quizStage = PipelineStage::updateOrCreate(
            [
                'job_post_id' => $job->id,
                'title' => 'Technical Assessment Quiz',
                'type' => 'assessment',
                'subtype' => 'quiz',
            ],
            [
                'sort_order' => 3,
                'instructions' => 'Candidates must pass a 5-question technical quiz. Passing score is 80%.',
                'is_enabled' => true,
                'config' => [
                    'passing_score' => 80,
                    'time_limit_minutes' => 15,
                ],
            ]
        );
        $quizStage->interviewers()->syncWithoutDetaching([$manager->id]);
        $this->seedQuizQuestions($quizStage);

        // 3. Interview: Online
        $onlineInterview = PipelineStage::updateOrCreate(
            [
                'job_post_id' => $job->id,
                'title' => 'Manager Online Interview',
                'type' => 'interview',
                'subtype' => 'online',
            ],
            [
                'sort_order' => 4,
                'instructions' => '30-minute culture and technical fit interview over Google Meet.',
                'is_enabled' => true,
            ]
        );
        $onlineInterview->interviewers()->syncWithoutDetaching([$manager->id]);

        // 4. Interview: Onsite (if applicable based on job type, let's just add it to all for thoroughness)
        if ($job->job_type !== 'remote') {
            $onsiteInterview = PipelineStage::updateOrCreate(
                [
                    'job_post_id' => $job->id,
                    'title' => 'Final Onsite Interview',
                    'type' => 'interview',
                    'subtype' => 'onsite',
                ],
                [
                    'sort_order' => 5,
                    'instructions' => 'In-person meeting with the wider team and final whiteboarding session.',
                    'is_enabled' => true,
                ]
            );
            $onsiteInterview->interviewers()->syncWithoutDetaching([$manager->id]);
        }
    }

    private function seedQuizQuestions(PipelineStage $stage)
    {
        // Prevent duplicate questions if seeding multiple times
        if ($stage->quizQuestions()->exists()) {
            return;
        }

        $questionsData = [
            [
                'question_text' => 'Which HTTP method should be used to partially update a resource?',
                'options' => [
                    ['text' => 'GET', 'is_correct' => false],
                    ['text' => 'POST', 'is_correct' => false],
                    ['text' => 'PUT', 'is_correct' => false],
                    ['text' => 'PATCH', 'is_correct' => true],
                ],
            ],
            [
                'question_text' => 'In standard relational database design, what ensures each row is uniquely identifiable?',
                'options' => [
                    ['text' => 'Primary Key', 'is_correct' => true],
                    ['text' => 'Foreign Key', 'is_correct' => false],
                    ['text' => 'Index', 'is_correct' => false],
                    ['text' => 'Unique Constraint', 'is_correct' => false],
                ],
            ],
            [
                'question_text' => 'What is the primary purpose of version control systems like Git?',
                'options' => [
                    ['text' => 'To compile source code into executables', 'is_correct' => false],
                    ['text' => 'To track changes in source code over time', 'is_correct' => true],
                    ['text' => 'To automatically deploy code to servers', 'is_correct' => false],
                    ['text' => 'To scan code for security vulnerabilities', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($questionsData as $index => $qData) {
            $question = QuizQuestion::create([
                'pipeline_stage_id' => $stage->id,
                'question' => $qData['question_text'],
                'sort_order' => $index + 1,
            ]);

            foreach ($qData['options'] as $opt) {
                QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'option_text' => $opt['text'],
                    'is_correct' => $opt['is_correct'],
                ]);
            }
        }
    }
}
