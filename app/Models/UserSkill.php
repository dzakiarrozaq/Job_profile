<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSkill extends Model
{
    use HasFactory;

    public $timestamps = false; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',          
        'skill_name',       
        'years_experience', 
        'certification',    
    ];

    public function user()
    /**
     * Mendapatkan data user pemilik skill ini.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    {
        return $this->belongsTo(User::class);
    }
}