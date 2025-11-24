<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCompetency extends Model
{
    use HasFactory;
    public $timestamps = false;


    protected $fillable = [
        'job_profile_id',
        'competency_master_id',
        'ideal_level',
        'weight',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(CompetenciesMaster::class, 'competency_master_id');
    }

    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }
}