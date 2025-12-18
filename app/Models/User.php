<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Import Model yang dibutuhkan
use App\Models\Role; 
use App\Models\Position;
use App\Models\Department;
use App\Models\GapRecord;
use App\Models\TrainingPlan;
use App\Models\EmployeeProfile;
use App\Models\JobProfile;

// Import Relation Classes
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Relations\HasOne; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'batch_number',
        'department_id',
        'position_id',
        'role_id',
        'phone_number',
        'hiring_date',  
        'gender',            
        'profile_photo_path',
        'manager_id',
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

    // =================================================================
    // 1. SISTEM ROLE & HAK AKSES (MANUAL FIX)
    // =================================================================

    /**
     * Relasi Many-to-Many ke tabel Roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    /**
     * Cek apakah user punya role tertentu.
     * Dipakai di Middleware & Controller: $user->hasRole('Admin')
     * [PENTING] Fungsi ini wajib ada untuk menggantikan Spatie
     */
    public function hasRole($roleName)
    {
        // Jika input array (['Admin', 'Supervisor'])
        if (is_array($roleName)) {
            return $this->roles()->whereIn('name', $roleName)->exists();
        }

        // Jika input string ('Admin')
        return $this->roles()->where('name', $roleName)->exists();
    }

    // =================================================================
    // 2. RELASI STRUKTUR ORGANISASI
    // =================================================================

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

    // =================================================================
    // 3. RELASI SISTEM KOMPETENSI
    // =================================================================

    // Relasi Job Profile (User -> Position -> JobProfile)
    public function jobProfile()
    {
         return $this->hasOneThrough(
            JobProfile::class,
            Position::class,
            'id',          // FK di positions (biasanya id)
            'position_id', // FK di job_profiles
            'position_id', // Local key di users
            'id'           // Local key di positions
        );
    }

    // Relasi ke Employee Profile (Singular/Tunggal - Nama Asli)
    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    // [ALIAS FIX] Alias Employee Profiles (Plural/Jamak)
    // Agar controller yang memanggil ->employeeProfiles aman
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

    // =================================================================
    // 4. RELASI DATA KARYAWAN LAINNYA
    // =================================================================

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