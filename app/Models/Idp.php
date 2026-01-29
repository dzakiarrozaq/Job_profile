<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idp extends Model {
    use HasFactory;

    // Aman: Mengizinkan semua kolom diisi (termasuk successor_position)
    protected $guarded = []; 

    public function user() { return $this->belongsTo(User::class); }

    public function details() { return $this->hasMany(IdpDetail::class); }

    protected $casts = [
        'career_aspirations' => 'array', // Wajib: convert JSON <-> Array
        'approved_at' => 'datetime',     // Tambahan: agar jadi objek Carbon
    ];
}