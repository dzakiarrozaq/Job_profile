<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    use HasFactory;

    /**
     * timestamps (created_at, updated_at) tidak digunakan di tabel ini.
     */
    public $timestamps = false; // Sesuai skema DBML Anda

    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * ========================================================
     * ## TAMBAHKAN ARRAY $fillable INI ##
     * ========================================================
     *
     * Kolom yang diizinkan untuk diisi secara massal (mass assignment).
     */
    protected $fillable = [
        'user_id',
        'competency_code',
        'competency_name',
        'current_level',
        'submitted_level', // <-- Ini yang menyebabkan error
        'status',
        'submitted_at',
        'verified_by',
        'verified_at',
        'reviewer_notes',
    ];
    // ========================================================


    /**
     * Mendapatkan data user pemilik profil kompetensi ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan data atasan yang memverifikasi.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}