<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_id',
        'created_by',
        'version',
        'notes',
        'tujuan_jabatan', 
        'wewenang',
        'dimensi_keuangan',   
        'dimensi_non_keuangan',     
        'status',         
        'dimensi_keuangan',
        'dimensi_non_keuangan',
    ];

    public function position(): BelongsTo {
        return $this->belongsTo(Position::class);
    }
    
    public function competencies(): HasMany {
        return $this->hasMany(JobCompetency::class);
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==============================================
    // ## TAMBAHKAN DUA FUNGSI RELASI INI ##
    // ==============================================

    /**
     * Mendapatkan semua tanggung jawab untuk job profile ini.
     */
    public function responsibilities(): HasMany
    {
        return $this->hasMany(JobResponsibility::class);
    }

    /**
     * Mendapatkan semua spesifikasi untuk job profile ini.
     */
    public function specifications(): HasMany
    {
        return $this->hasMany(JobSpecification::class);
    }
    
    // (Anda mungkin juga perlu 'workRelations' nanti)
    public function workRelations(): HasMany
    {
        return $this->hasMany(JobWorkRelation::class);
    }
}