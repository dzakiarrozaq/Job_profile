<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRequirement extends Model
{
    use HasFactory;

    /**
     * timestamps (created_at, updated_at) tidak digunakan di tabel ini.
     */
    public $timestamps = false; 

    protected $fillable = [
        'job_profile_id',
        'competency_code',
        'competency_name',
        'ideal_level',
        'weight',
    ];



    /**
     * Mendapatkan data Job Profile induk dari requirement ini.
     */
    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }
}