<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GapRecord extends Model
{
    use HasFactory;

    /**
     * timestamps (created_at, updated_at) tidak digunakan di tabel ini.
     */
    public $timestamps = false; // Sesuai skema DBML Anda

    /**
     * Kolom yang bisa diisi.
     */
    protected $fillable = [
        'user_id',
        'job_profile_id',
        'competency_code',
        'competency_name', // <-- TAMBAHKAN BARIS INI
        'ideal_level',
        'current_level',
        'gap_value',
        'weighted_gap',
        'calculated_at',
    ];

    /**
     * Mendapatkan data user pemilik gap record ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan data job profile terkait gap record ini.
     */
    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }
}