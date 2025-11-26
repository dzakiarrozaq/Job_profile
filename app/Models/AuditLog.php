<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id', 
        'description', 'timestamp', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public static function record($action, $description, $model = null)
    {
        self::create([
            'user_id'     => Auth::id(), 
            'action'      => $action,    
            'description' => $description, 
            'entity_type' => $model ? get_class($model) : null, 
            'entity_id'   => $model ? $model->id : null, 
            'timestamp'   => now(),
            'ip_address'  => Request::ip(), 
            'user_agent'  => Request::userAgent(), 
        ]);
    }
}