<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;
    
    // (Tambahkan $fillable jika Anda belum)
    protected $fillable = [
        'title',
        'department_id',
        'job_grade_id',
        'directorate_id',
        'unit_id',
        'section_id',
        'atasan_id',
        'description',
    ];

    /**
     * Mendapatkan departemen tempat posisi ini berada.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Mendapatkan Job Profile yang terkait dengan posisi ini.
     */
    public function jobProfile(): HasOne
    {
        return $this->hasOne(JobProfile::class)->latestOfMany();
    }

    /**
     * Mendapatkan semua user yang memiliki posisi ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ==============================================
    // ## TAMBAHKAN SEMUA FUNGSI RELASI DI BAWAH INI ##
    // ==============================================

    /**
     * Mendapatkan Job Grade dari posisi ini.
     */
    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }

    /**
     * Mendapatkan Direktorat dari posisi ini.
     */
    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    /**
     * Mendapatkan Unit dari posisi ini.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Mendapatkan Seksi dari posisi ini.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Mendapatkan atasan (posisi) dari posisi ini.
     */
    public function atasan(): BelongsTo
    {
        // Relasi ke tabel 'positions' sendiri
        return $this->belongsTo(Position::class, 'atasan_id');
    }
}