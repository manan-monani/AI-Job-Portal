<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPost extends BaseModel
{
    /** @use HasFactory<\Database\Factories\JobPostFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'title',
        'slug',
        'description',
        'salary_type',
        'salary_period',
        'min_salary',
        'max_salary',
        'min_experience',
        'max_experience',
        'job_type',
        'location',
        'deadline',
        'employment_type',
        'internship_duration',
        'contract_duration',
        'working_hours',
        'status',
        'hiring_status',
        'pipeline_enabled',
        'apply_instructions',
        'is_cv_required',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($jobPost) {
            $jobPost->slug = \Illuminate\Support\Str::slug($jobPost->title).'-'.\Illuminate\Support\Str::random(5);
        });
    }

    public function applications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    protected $casts = [
        'deadline' => 'date',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'min_experience' => 'decimal:2',
        'max_experience' => 'decimal:2',
        'pipeline_enabled' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'job_post_tag');
    }

    public function pipelineStages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('sort_order');
    }

    public function getHiringStatusAttribute(): string
    {
        $stored = $this->attributes['hiring_status'] ?? 'in_progress';

        // Admin final decisions take precedence
        if (in_array($stored, ['hired_closed', 'not_hired_closed', 'continue_hiring'])) {
            return match ($stored) {
                'hired_closed' => 'Hired & Closed',
                'not_hired_closed' => 'Not Hired & Closed',
                'continue_hiring' => 'Continue Hiring',
                default => ucfirst($stored),
            };
        }

        if ($this->status !== 'published') {
            return ucfirst($this->status);
        }

        if ($this->deadline && $this->deadline->isPast()) {
            return 'Closed';
        }

        $applicationCount = $this->applications()->count();
        if ($applicationCount === 0) {
            return 'No Applications';
        }

        $hasProgress = CandidateStageStatus::whereHas('stage', function ($q) {
            $q->where('job_post_id', $this->id)
                ->where('sort_order', '>', 1);
        })->exists();

        if ($hasProgress) {
            return 'Hiring In Progress';
        }

        return 'Start Hiring';
    }

    public function atsScores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApplicationAtsScore::class);
    }
}
