<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Models\Role; 
use App\Models\Position;
use App\Models\Department;
use App\Models\GapRecord;
use App\Models\TrainingPlan;
use App\Models\EmployeeProfile;
use App\Models\JobProfile;

use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Relations\HasOne; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id', 
        'position_id',
        'manager_id',
        'status',
        'gender',
        'profile_photo_path',
        'phone_number',
        'hiring_date',
        'batch_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    /**
     * Relasi Many-to-Many ke tabel Roles.
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class, 
            'model_has_roles',       
            'model_id',              
            'role_id'                
        )->wherePivot('model_type', 'App\Models\User'); 
    }

    /**
     * Cek apakah user punya role tertentu.
     * Dipakai di Middleware & Controller: $user->hasRole('Admin')
     * [PENTING] Fungsi ini wajib ada untuk menggantikan Spatie
     */
    public function hasRole($roleName)
    {
        if (is_array($roleName)) {
            return $this->roles()->whereIn('name', $roleName)->exists();
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    public function position(): BelongsTo 
    {
        return $this->belongsTo(Position::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function jobProfile()
    {
         return $this->hasOneThrough(
            JobProfile::class,
            Position::class,
            'id',          
            'position_id', 
            'position_id', 
            'id'           
        );
    }

    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    
    public function employeeProfiles(): HasOne
    {
        return $this->employeeProfile();
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
    
}