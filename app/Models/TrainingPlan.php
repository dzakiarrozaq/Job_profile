<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- 1. Import ini

class TrainingPlan extends Model
{
    use HasFactory;

    protected $casts = [
        'submitted_at' => 'datetime',
        'supervisor_approved_at' => 'datetime',
        'lp_approved_at' => 'datetime',
    ];

    /**
     * Mendapatkan data user pemilik rencana ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==============================================
    // ## 2. TAMBAHKAN FUNGSI INI (JIKA BELUM ADA) ##
    // ==============================================

    /**
     * Mendapatkan semua item (pelatihan) dalam rencana ini.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TrainingPlanItem::class);
    }
}