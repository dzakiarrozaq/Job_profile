<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',            
        'competency_name',  
        'level',            
        'duration',         
        'objective',        
        'content',          
        'provider',         
        'status', 
        'delivery_method',  
    ];

    public function getDescriptionAttribute()
    {
        $desc = '';

        // 1. Tambahkan Objective jika ada
        if (!empty($this->objective)) {
            $desc .= "Tujuan:\n" . $this->objective . "\n\n";
        }

        // 2. Tambahkan Content jika ada
        if (!empty($this->content)) {
            $desc .= "Materi:\n" . $this->content;
        }

        // 3. Jika keduanya kosong
        if (empty($desc)) {
            return 'Tidak ada deskripsi detail.';
        }

        return $desc;
    }

    // Casts opsional (jika duration mau dipastikan string/angka)
    protected $casts = [
        // 'duration' => 'integer', // Aktifkan jika ingin durasi dianggap angka saja
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}