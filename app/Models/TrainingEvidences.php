<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingEvidences extends Model
{
    use HasFactory;

    protected $casts = [
        'verified_at' => 'datetime',
        'created_at' => 'datetime', // <-- Tambahkan ini jika Anda menambahkan kolomnya
        'updated_at' => 'datetime', // <-- Tambahkan ini jika Anda menambahkan kolomnya
    ];
}
