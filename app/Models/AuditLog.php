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
            'user_id'     => Auth::id(), // Siapa yang melakukan
            'action'      => $action,    // Apa tindakannya (misal: "Update Job Profile")
            'description' => $description, // Detailnya
            'entity_type' => $model ? get_class($model) : null, // Objek apa yang diubah
            'entity_id'   => $model ? $model->id : null, // ID objek tersebut
            'timestamp'   => now(),
            'ip_address'  => Request::ip(), // IP Address user
            'user_agent'  => Request::userAgent(), // Browser user
        ]);
    }
}