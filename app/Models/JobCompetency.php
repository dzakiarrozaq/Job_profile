<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// TAMBAHKAN BARIS INI:
use App\Models\CompetenciesMaster; 

class JobCompetency extends Model
{
    use HasFactory;

    protected $table = 'job_competencies';

    protected $fillable = [
        'job_profile_id',
        'competency_master_id',
        'type',
        'competency_name',
        'competency_code',      
        'ideal_level',          
        'weight'
    ];

    public function jobProfile()
    {
        return $this->belongsTo(JobProfile::class);
    }
    
    // Ini sudah benar
    public function competency()
    {
        return $this->belongsTo(CompetenciesMaster::class, 'competency_master_id');
    }
}