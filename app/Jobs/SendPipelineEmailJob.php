<?php

namespace App\Jobs;

use App\Models\MailLog;
use App\Services\MailCommunicationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPipelineEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Number of times the queued job may be attempted.
     */
    public int $tries = 3;

    /**
     * Backoff intervals in seconds between retries.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 300];

    public function __construct(
        public int $mailLogId,
        public string $body,
    ) {}

    public function handle(MailCommunicationService $service): void
    {
        $mailLog = MailLog::find($this->mailLogId);

        if (! $mailLog || $mailLog->status === 'sent') {
            return;
        }

        $service->dispatchMail($mailLog, $this->body);
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(?\Throwable $exception): void
    {
        $mailLog = MailLog::find($this->mailLogId);

        if ($mailLog) {
            $mailLog->markFailed($exception?->getMessage() ?? 'Unknown error after all retries.');
        }

        Log::error('SendPipelineEmailJob: permanently failed', [
            'mail_log_id' => $this->mailLogId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
