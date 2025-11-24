<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Tambahkan ini

class Department extends Model
{
    use HasFactory;
    public $timestamps = false; // Asumsi dari DBML Anda

    /**
     * Mendapatkan semua posisi di dalam departemen ini.
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}