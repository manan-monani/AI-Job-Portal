<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationResumeParse extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id',
        'raw_text',
        'parsed_json',
        'parser_version',
        'status',
        'error_message',
        'parsed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parsed_json' => 'array',
            'parsed_at' => 'datetime',
        ];
    }

    public function application(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }
}
