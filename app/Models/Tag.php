<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends BaseModel
{
    protected $fillable = ['name', 'description', 'status'];

    public function jobPosts(): BelongsToMany
    {
        return $this->belongsToMany(JobPost::class, 'job_post_tag');
    }

    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory, SoftDeletes;
}
