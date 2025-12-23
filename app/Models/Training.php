<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        // 1. Field Utama (Sesuai Form Admin)
        'title', 
        'provider', 
        'type', 
        'difficulty',   // <--- DITAMBAHKAN (Penting)
        'description', 
        'duration',     // <--- DISESUAIKAN (agar bisa input string "2 Jam")
        'link_url',     // <--- DISESUAIKAN (sesuai name di form)
        'status',

        // 2. Field Tambahan (Opsional / Future Proof)
        'tags', 
        'skill_tags',
        
        // 3. Field Approval (Untuk fitur pengajuan karyawan nanti)
        'created_by', 
        'supervisor_approver_id', 
        'supervisor_approved_at', 
        'lp_approver_id', 
        'lp_approved_at', 
        'rejection_notes'
    ];

    protected $casts = [
        'supervisor_approved_at' => 'datetime',
        'lp_approved_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}