<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobHistory extends Model
{
    protected $fillable = ['user_id', 'title', 'unit', 'start_date', 'end_date', 'description'];
}
