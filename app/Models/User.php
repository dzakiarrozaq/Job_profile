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

use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * (Ini sudah benar)
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'batch_number',
        'department_id',
        'position_id',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * (Ini sudah benar)
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     * (Ini sudah benar)
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function position(): BelongsTo // <-- Ditambahkan
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Mendapatkan Departemen dari user.
     */
    public function department(): BelongsTo // <-- Ditambahkan
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Mendapatkan Atasan (Supervisor) dari user.
     */
    public function manager(): BelongsTo // <-- Ditambahkan
    {
        // Ini adalah relasi ke tabel 'users' sendiri
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Mendapatkan data Gap Kompetensi milik user.
     */
    public function gapRecords(): HasMany
    {
        return $this->hasMany(GapRecord::class);
    }

    /**
     * Mendapatkan Rencana Pelatihan (Training Plans) milik user.
     */
    public function trainingPlans(): HasMany // <-- Ditambahkan
    {
        return $this->hasMany(TrainingPlan::class);
    }

    /**
     * Mendapatkan data profil kompetensi (self-assessment) milik user.
     */
    public function employeeProfiles(): HasMany
    {
        return $this->hasMany(EmployeeProfile::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
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