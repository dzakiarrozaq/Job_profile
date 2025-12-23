<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Idp extends Model {
    protected $guarded = [];

    // Relasi ke User
    public function user() { return $this->belongsTo(User::class); }

    // Relasi ke Detail (Goals)
    public function details() { return $this->hasMany(IdpDetail::class); }
}