<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationAtsScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id',
        'job_post_id',
        'pipeline_stage_id',
        'total_score',
        'passed',
        'pass_reason',
        'criteria_breakdown',
        'scoring_version',
        'scored_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_score' => 'decimal:2',
            'passed' => 'boolean',
            'criteria_breakdown' => 'array',
            'scored_at' => 'datetime',
        ];
    }

    public function application(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function jobPost(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function pipelineStage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }
}
