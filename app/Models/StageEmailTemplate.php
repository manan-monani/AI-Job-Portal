<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageEmailTemplate extends BaseModel
{
    protected $fillable = [
        'pipeline_stage_id',
        'subject',
        'body',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }
}
