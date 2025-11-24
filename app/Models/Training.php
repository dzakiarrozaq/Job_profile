<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'provider', 'type', 'description', 'link', 'tags', 'duration_hours', 'skill_tags', 'status',
        'created_by', 'supervisor_approver_id', 'supervisor_approved_at', 'lp_approver_id', 'lp_approved_at', 'rejection_notes'
    ];

    protected $casts = [
        'supervisor_approved_at' => 'datetime',
        'lp_approved_at' => 'datetime',
    ];

    // Relasi ke pembuat (User yang mengusulkan)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}