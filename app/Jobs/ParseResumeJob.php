<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ParseResumeJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $applicationId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        \App\Services\ResumeTextExtractorService $extractor,
        \App\Services\ResumeNormalizerService $normalizer
    ): void {
        $application = \App\Models\JobApplication::with('resume')->find($this->applicationId);

        if (! $application) {
            return;
        }

        // Initialize or get the parse record
        $parseRecord = \App\Models\ApplicationResumeParse::firstOrCreate(
            ['job_application_id' => $application->id],
            [
                'status' => 'pending',
                'parser_version' => '1.0.0',
            ]
        );

        if ($parseRecord->status === 'done') {
            \App\Jobs\ScoreApplicationJob::dispatch($application->id);

            return; // Already parsed
        }

        // Cancellation check
        if ($application->ats_state !== 'processing' && $application->ats_state !== 'pending') {
            \Illuminate\Support\Facades\Log::info("ParseResumeJob skipped for Application {$application->id}: Scan was stopped/reset.");

            return;
        }

        $parseRecord->update(['status' => 'processing']);

        try {
            $rawText = null;

            // 1. Try to extract text from an uploaded CV file if it exists
            if ($application->resume && $application->resume->file_path) {
                $rawText = $extractor->extractText($application->resume->file_path);
            }

            // 2. Fallback to using the cover-letter / message as the "resume text" if no file text could be extracted
            if (empty(trim((string) $rawText))) {
                $rawText = (string) $application->message;
            }

            // 3. Normalize and structure
            $parsedJson = null;
            if (! empty(trim($rawText))) {
                $parsedJson = $normalizer->parse($rawText);
            }

            // 4. Save
            $parseRecord->update([
                'raw_text' => $rawText,
                'parsed_json' => $parsedJson,
                'status' => 'done',
                'parsed_at' => now(),
            ]);

            // Dispatch scoring job automatically after parsing
            \App\Jobs\ScoreApplicationJob::dispatch($application->id);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to parse resume for Application ID {$this->applicationId}: ".$e->getMessage());

            $application->update(['ats_state' => 'failed']);
            $parseRecord->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
