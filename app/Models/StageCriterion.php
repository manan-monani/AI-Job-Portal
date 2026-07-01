<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageCriterion extends BaseModel
{
    protected $table = 'stage_criteria';

    protected $fillable = [
        'pipeline_stage_id',
        'label',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }
}
