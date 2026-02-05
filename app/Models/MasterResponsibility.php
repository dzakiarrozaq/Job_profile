<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterResponsibility extends Model
{
    protected $fillable = ['job_grade_id', 'type', 'responsibility', 'expected_outcome'];

    public function jobGrade()
    {
        return $this->belongsTo(JobGrade::class);
    }
}