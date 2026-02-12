<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingPlan extends Model
{
    use HasFactory;

    protected $guarded = ['id']; 

    protected $casts = [
        'submitted_at' => 'datetime',
        'supervisor_approved_at' => 'datetime',
        'lp_approved_at' => 'datetime',
        'approved_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TrainingPlanItem::class);
    }

    // --- TAMBAHKAN INI ---
    // Relasi untuk mengambil Data Supervisor yang melakukan Approval
    public function approver(): BelongsTo
    {
        // Pastikan Anda sudah membuat kolom 'approved_by' di database
        return $this->belongsTo(User::class, 'approved_by');
    }
}