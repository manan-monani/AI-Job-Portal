<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobApplication extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'job_post_id',
        'user_id',
        'name',
        'email',
        'phone',
        'address', // Added from the code edit snippet
        'city',    // Added from the code edit snippet
        'message',
        'status',
        'ats_state',
        'ats_score_cached',
        'ats_passed_cached',
    ];

    protected function casts(): array
    {
        return [
            'ats_score_cached' => 'float',
            'ats_passed_cached' => 'boolean',
        ];
    }

    public function jobPost(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function interviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resume(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Resume::class);
    }

    public function stageStatuses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CandidateStageStatus::class);
    }

    public function atsScores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApplicationAtsScore::class);
    }

    public function atsOverrides(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApplicationAtsOverride::class);
    }

    public function resumeParses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApplicationResumeParse::class);
    }

    public function stageSubmissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StageSubmission::class);
    }
}
