<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <--- Import ini

class CompetenciesMaster extends Model
{
    use HasFactory;

    protected $table = 'competencies_master';

    // HAPUS BARIS INI: public $timestamps = false;
    // (Karena di migration Anda ada $table->timestamps(), sebaiknya fitur ini aktif)

    /**
     * Kolom yang bisa diisi.
     */
    protected $fillable = [
        'competency_code',
        'competency_name', // Nama Kompetensi (Strategic Management)
        'type',            // Tipe (Soft/Hard/Managerial)
        'description',     // <--- TAMBAHAN PENTING (Untuk Definisi)
    ];

    /**
     * Relasi ke Tabel Child (Perilaku Kunci)
     * Satu Kompetensi punya Banyak Level Perilaku
     */
    public function keyBehaviors(): HasMany
    {
        // Parameter: (Model Tujuan, Nama Foreign Key di tabel tujuan)
        return $this->hasMany(CompetencyKeyBehavior::class, 'competency_master_id');
    }
}