<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetenciesMaster extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang benar (karena default-nya akan 'competencies_masters').
     */
    protected $table = 'competencies_master';

    /**
     * Tabel ini tidak menggunakan timestamps (created_at/updated_at).
     */
    public $timestamps = false; // Sesuai migrasi Anda

    /**
     * Kolom yang bisa diisi.
     */
    protected $fillable = [
        'competency_code',
        'competency_name',
        'type',
    ];
}