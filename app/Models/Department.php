<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; 

class Department extends Model
{
    use HasFactory;

    // (Sesuaikan $fillable Anda jika ada)
    // protected $fillable = ['name', 'description'];

    /**
     * Mendapatkan semua user yang ada di departemen ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Mendapatkan semua posisi di departemen ini (Opsional, tapi bagus).
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Mendapatkan unit-unit di departemen ini.
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}