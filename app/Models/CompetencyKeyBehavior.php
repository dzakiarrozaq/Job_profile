<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetencyKeyBehavior extends Model
{
    // Pastikan nama tabel sesuai migration yang baru dibuat
    protected $table = 'competency_key_behaviors';

    protected $fillable = [
        'competency_master_id', // FK ke Master
        'level',                // 1, 2, 3, 4, 5
        'behavior'              // Isi teks perilaku
    ];

    // Relasi balik ke Master (Opsional, tapi berguna)
    public function master(): BelongsTo
    {
        return $this->belongsTo(CompetenciesMaster::class, 'competency_master_id');
    }
}