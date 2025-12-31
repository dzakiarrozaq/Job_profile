<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingPlanItem extends Model
{
    use HasFactory;

    // --- PASTIKAN BARIS INI ADA ---
    protected $guarded = ['id'];
    // ------------------------------

    public function plan()
    {
        return $this->belongsTo(TrainingPlan::class, 'training_plan_id');
    }

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id');
    }
}