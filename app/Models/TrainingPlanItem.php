<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- 1. Import ini

class TrainingPlanItem extends Model
{
    use HasFactory;

    // ==============================================
    // ## 2. TAMBAHKAN DUA FUNGSI INI ##
    // ==============================================

    /**
     * Mendapatkan data rencana (keranjang) induk.
     */
    public function trainingPlan(): BelongsTo
    {
        return $this->belongsTo(TrainingPlan::class);
    }

    /**
     * Mendapatkan data detail pelatihan (dari katalog).
     * Ini adalah relasi yang hilang yang menyebabkan error.
     */
    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }
}