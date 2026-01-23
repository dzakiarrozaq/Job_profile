<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCompetency extends Model
{
    use HasFactory;

    protected $table = 'job_competencies';

    protected $fillable = [
        'job_profile_id',
        'competency_master_id', // Pastikan ini sesuai kolom di DB
        'competency_name',
        'competency_code',      
        'ideal_level',          
        'weight'
    ];

    public function jobProfile()
    {
        return $this->belongsTo(JobProfile::class);
    }
    
    // --- PERBAIKAN: NAMA FUNGSI HARUS 'competency' ---
    // Agar sesuai dengan controller yang memanggil ->load('competency')
    public function competency()
    {
        // Sesuaikan 'CompetenciesMaster' dengan nama Model Master Kompetensi Mas
        // Sesuaikan 'competency_master_id' dengan nama kolom di tabel job_competencies
        return $this->belongsTo(CompetenciesMaster::class, 'competency_master_id');
    }
}