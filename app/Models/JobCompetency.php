<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCompetency extends Model
{
    use HasFactory;

    protected $table = 'job_competencies';

    // PASTIKAN SEMUA KOLOM INI ADA DI SINI:
    protected $fillable = [
        'job_profile_id',
        'competency_master_id', // <--- INI WAJIB ADA
        'competency_name',
        'competency_code',      // <--- Tambahkan ini juga
        'ideal_level',          // <--- JANGAN LUPA INI
        'weight'
    ];

    public function jobProfile()
    {
        return $this->belongsTo(JobProfile::class);
    }
    
    public function master()
    {
        return $this->belongsTo(CompetenciesMaster::class, 'competency_master_id');
    }
}