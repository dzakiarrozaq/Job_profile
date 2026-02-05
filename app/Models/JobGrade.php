<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobGrade extends Model
{
    use HasFactory;

    // --- TAMBAHKAN BARIS INI ---
    protected $fillable = [
        'name', 
        // Tambahkan kolom lain jika ada, misal 'description'
    ];
}
