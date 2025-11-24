<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobWorkRelation extends Model
{
    use HasFactory;
    public $timestamps = false; // Sesuai DBML

    protected $fillable = [
        'job_profile_id',
        'type',
        'unit_instansi',
        'purpose',
    ];

    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }
}
