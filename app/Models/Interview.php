<?php

namespace App\Models;

class Interview extends BaseModel
{
    protected $fillable = [
        'job_application_id',
        'interviewer_id',
        'scheduled_at',
        'location',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function application(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function interviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
