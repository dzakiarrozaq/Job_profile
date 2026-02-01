<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import ini

class Organization extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
        return $this->belongsTo(Organization::class, 'parent_id');
    }
}