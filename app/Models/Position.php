<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Organization; 

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'code', 'tipe', 'organization_id', 
        'job_grade_id', 'atasan_id', 'description',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'atasan_id');
    }

    /**
     * RELASI TUNGGAL: Gunakan 'subordinates' sebagai standar
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Position::class, 'atasan_id');
    }

    /**
     * ALIAS (Opsional): Jika Anda tetap ingin nama lain tanpa menulis ulang logic
     */
    public function bawahan() { return $this->subordinates(); }

    public function user(): HasOne
    {
        // Tips: Latest() memastikan jika ada transisi jabatan, yang diambil yang paling baru
        return $this->hasOne(User::class)->latestOfMany(); 
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class, 'job_grade_id');
    }

    public function jobProfile(): HasOne
    {
        return $this->hasOne(JobProfile::class)->latestOfMany();
    }

    /**
     * Mendapatkan semua ID posisi di bawah posisi ini (Recursive)
     * Ditambahkan proteksi agar tidak error jika relasi subordinates belum ter-load
     */
    public function getAllSubordinateIds()
    {
        $ids = [];

        // Eager load subordinates untuk level saat ini jika belum ada
        if (!$this->relationLoaded('subordinates')) {
            $this->load('subordinates');
        }

        foreach ($this->subordinates as $subordinate) {
            $ids[] = $subordinate->id;
            // Rekursif ke bawah
            $ids = array_merge($ids, $subordinate->getAllSubordinateIds());
        }

        return $ids;
    }

    public function technicalStandards()
    {
        return $this->hasMany(PositionTechnicalStandard::class, 'position_id');
    }

    public function bawahanRecursive()
    {
        // Memanggil relasi 'bawahan' lalu memanggil dirinya sendiri (recursive)
        return $this->bawahan()->with('bawahanRecursive');
    }
}