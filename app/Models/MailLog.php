<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailLog extends BaseModel
{
    protected $fillable = [
        'recipient_email',
        'subject',
        'job_application_id',
        'pipeline_stage_id',
        'job_post_id',
        'send_mode',
        'status',
        'error_message',
        'triggered_by',
        'admin_user_id',
        'idempotency_key',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function markSent(): void
    {
        $this->update(['status' => 'sent', 'attempts' => $this->attempts + 1]);
    }

    public function markFailed(string $error): void
    {
        $this->update(['status' => 'failed', 'error_message' => $error, 'attempts' => $this->attempts + 1]);
    }
}
