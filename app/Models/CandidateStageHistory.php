<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateStageHistory extends BaseModel
{
    protected $fillable = [
        'candidate_stage_status_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
    ];

    public function candidateStageStatus(): BelongsTo
    {
        return $this->belongsTo(CandidateStageStatus::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
