<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionTechnicalStandard extends Model
{
    // Nama tabel jika Anda tidak mengikuti standar jamak Laravel
    protected $table = 'position_technical_standards';

    protected $fillable = [
        'position_id',
        'competency_master_id',
        'ideal_level'
    ];

    public $timestamps = true;

    // Relasi ke Posisi
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    // Relasi ke Master Kompetensi
    public function competencyMaster()
    {
        return $this->belongsTo(CompetenciesMaster::class, 'competency_master_id');
    }
}