<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StageSubmission extends Model
{
    protected $fillable = [
        'job_application_id',
        'pipeline_stage_id',
        'status',
        'file_path',
        'text_answer',
        'obtained_mark',
        'total_mark',
        'quiz_results',
        'started_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'quiz_results' => 'array',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'obtained_mark' => 'float',
            'total_mark' => 'float',
        ];
    }

    public function application(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function stage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }
}
