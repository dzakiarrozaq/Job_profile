<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobResponsibility extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'job_profile_id',
        'description',
        'expected_result',
    ];

    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }
}