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
        'provider', 
    
        'type',   
        'cost',     

        'type',       
        'difficulty',  
        'description',
        'duration',    
        'link_url',    
        'status',

        'tags', 
        'skill_tags',
        
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
        'cost' => 'integer', 
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}