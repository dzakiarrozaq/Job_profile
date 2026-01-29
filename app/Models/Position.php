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
        'title',
        'code',
        'tipe',
        'organization_id', 
        'job_grade_id',
        'atasan_id',
        'description',
    ];

    /**
     * RELASI UTAMA: Mengetahui Posisi ini ada di Unit/Dept/Section mana.
     * Menggantikan function department(), unit(), section() yang lama.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Mendapatkan Jabatan Atasan (Parent Position).
     * Contoh: "Staff IT" punya atasan "IT Manager".
     */
    public function atasan(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'atasan_id');
    }

    /**
     * Kebalikannya: Mendapatkan daftar bawahan dari posisi ini.
     * Berguna untuk melihat struktur ke bawah.
     */
    public function bawahan(): HasMany
    {
        return $this->hasMany(Position::class, 'atasan_id');
    }

    /**
     * Mendapatkan SATU user yang aktif menjabat posisi ini.
     * Dipakai untuk logika: $user->position->atasan->user->name (Mencari Nama Boss)
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class)->latest(); // Ambil user terbaru/aktif
    }

    /**
     * Mendapatkan SEMUA user (History) yang pernah/sedang di posisi ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Mendapatkan Job Grade (Band 1, Band 2, dll).
     */
    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }

    /**
     * Mendapatkan Job Profile (Opsional/Legacy).
     */
    public function jobProfile(): HasOne
    {
        return $this->hasOne(JobProfile::class)->latestOfMany();
    }

    /**
     * RELASI TAMBAHAN: Untuk load struktur pohon (Tree View)
     * Memanggil relasi 'bawahan' secara berulang (recursive).
     */
    public function bawahanRecursive()
    {
        // Pastikan 'bawahan' sesuai dengan nama fungsi HasMany yang sudah ada
        return $this->bawahan()->with(['bawahanRecursive', 'organization']);
    }

    /**
     * Relasi ke Departemen
     */
    public function department(): BelongsTo
    {
        // Asumsi: ada kolom 'department_id' di tabel positions
        // Arahkan ke model Organization (karena biasanya dept adalah bagian dari organisasi)
        return $this->belongsTo(Organization::class, 'department_id');
    }

    /**
     * Relasi ke Unit
     */
    public function unit(): BelongsTo
    {
        // Asumsi: ada kolom 'unit_id'
        return $this->belongsTo(Organization::class, 'unit_id');
    }

    /**
     * Relasi ke Section (Seksi)
     */
    public function section(): BelongsTo
    {
        // Asumsi: ada kolom 'section_id'
        return $this->belongsTo(Organization::class, 'section_id');
    }

    /**
     * Relasi ke Direktorat (Tadi di View ada pemanggilan ->directorate->name)
     */
    public function directorate(): BelongsTo
    {
        // Asumsi: ada kolom 'directorate_id'
        return $this->belongsTo(Organization::class, 'directorate_id');
    }
}