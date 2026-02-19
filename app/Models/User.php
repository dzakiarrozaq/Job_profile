<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Relations\HasOne; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Position;
use App\Models\Role;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable; 

    protected $fillable = [
        'name',
        'email',
        'password',
        
        // --- STRUKTUR & DATA UTAMA ---
        'nik',           
        'position_id',  
        'status',
        'hiring_date',
        
        // --- DATA PRIBADI ---
        'gender',
        'place_of_birth',
        'date_of_birth',    
        'domicile',
        'phone_number',
        'profile_photo_path',
        'company_name',

        // --- AUTH ---
        'email_verified_at', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'nik' => 'encrypted',
            'hiring_date'       => 'date', 
            'birth_date'        => 'date',
        ];
    }

    // ==========================================
    // 1. RELASI UTAMA
    // ==========================================

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function position(): BelongsTo 
    {
        return $this->belongsTo(Position::class);
    }

    // ==========================================
    // 2. ACCESSORS (Untuk View)
    // ==========================================

    public function getUnitNameAttribute()
    {
        return $this->position?->organization?->name ?? '-';
    }

    public function getAtasanNameAttribute()
    {
        return $this->position?->atasan?->user?->name ?? '-';
    }

    public function getAtasanJabatanAttribute()
    {
        return $this->position?->atasan?->title ?? '-';
    }

    // ==========================================
    // 3. RELASI LAINNYA
    // ==========================================

    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function gapRecords(): HasMany
    {
        return $this->hasMany(GapRecord::class);
    }

    public function trainingPlans(): HasMany 
    {
        return $this->hasMany(TrainingPlan::class);
    }

    public function jobHistories() {
        return $this->hasMany(JobHistory::class)->orderBy('start_date', 'desc');
    }

    public function educationHistories() {
        return $this->hasMany(EducationHistory::class)->orderBy('year_end', 'desc');
    }

    public function skills() {
        return $this->hasMany(UserSkill::class);
    }

    public function interests() {
        return $this->hasMany(UserInterest::class);
    }

    public function hasRole($roleName)
    {
        return $this->roles->contains('name', $roleName);
    }

    public function manager()
    {
        // Opsi A: Jika manager_id ada langsung di tabel users
        // return $this->belongsTo(User::class, 'manager_id');

        // Opsi B: Jika struktur Anda menggunakan Position (User -> Position -> Parent Position -> User)
        return $this->hasOneThrough(
            User::class,
            Position::class,
            'id', 
            'position_id', 
            'position_id', 
            'atasan_id' 
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            if ($user->isDirty('nik') && $user->nik) {
                $user->nik_hash = hash_hmac('sha256', $user->nik, config('app.key'));
            }
        });
    }
}