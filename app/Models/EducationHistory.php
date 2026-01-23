<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationHistory extends Model
{
    protected $fillable = ['user_id', 'degree', 'institution', 'field_of_study', 'year_start', 'year_end'];
}
