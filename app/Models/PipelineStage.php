<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PipelineStage extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'job_post_id',
        'type',
        'subtype',
        'title',
        'instructions',
        'config',
        'sort_order',
        'is_system',
        'system_key',
        'is_enabled',
        'send_mail_on_trigger',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_enabled' => 'boolean',
            'send_mail_on_trigger' => 'boolean',
            'sort_order' => 'integer',
            'config' => 'array',
        ];
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function interviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'stage_interviewers')
            ->withTimestamps();
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(StageCriterion::class, 'pipeline_stage_id');
    }

    public function candidateStatuses(): HasMany
    {
        return $this->hasMany(CandidateStageStatus::class, 'pipeline_stage_id');
    }

    public function atsScores(): HasMany
    {
        return $this->hasMany(ApplicationAtsScore::class, 'pipeline_stage_id');
    }

    public function emailTemplate(): HasOne
    {
        return $this->hasOne(StageEmailTemplate::class, 'pipeline_stage_id');
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'pipeline_stage_id')->orderBy('sort_order');
    }
}
