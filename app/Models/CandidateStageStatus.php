<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CandidateStageStatus extends BaseModel
{
    protected $fillable = [
        'pipeline_stage_id',
        'job_application_id',
        'status',
        'score',
        'notes',
        'actioned_by',
        'actioned_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'actioned_at' => 'datetime',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function actionedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CandidateStageHistory::class);
    }
}
