<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import ini

class Organization extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // --- TAMBAHKAN KODE INI ---
    /**
     * Relasi ke Position
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Relasi ke Induk Organisasi (Parent)
     * Contoh: Unit -> Parent-nya Section -> Parent-nya Dept
     */
    public function parent()
    {
        // Asumsi: ada kolom 'parent_id' di tabel organizations
        return $this->belongsTo(Organization::class, 'parent_id');
    }
}