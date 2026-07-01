<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationAtsOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id',
        'pipeline_stage_id',
        'overridden_by',
        'original_passed',
        'overridden_passed',
        'reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'original_passed' => 'boolean',
            'overridden_passed' => 'boolean',
        ];
    }

    public function application(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function pipelineStage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function overrider(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }
}
