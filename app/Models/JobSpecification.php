<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSpecification extends Model
{
    use HasFactory;
    public $timestamps = false; 

    protected $fillable = [
        'job_profile_id',
        'type',
        'requirement',
        'level_or_notes',
    ];

    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }
}