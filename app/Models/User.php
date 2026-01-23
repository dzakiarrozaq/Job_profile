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

// PENTING: Import Model yang dibutuhkan
use App\Models\Position;
use App\Models\Role;
// use Spatie\Permission\Traits\HasRoles; // <-- MATIKAN INI jika pakai role_id manual

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable; 
    // use HasRoles; // <-- Matikan trait Spatie karena kita pakai simple role_id

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        
        // --- STRUKTUR BARU ---
        'role_id',      // Pengganti Spatie/String Role
        'position_id',  // Kunci utama struktur organisasi
        // 'department_id', // HAPUS (Sudah tidak pakai)
        // 'manager_id',    // HAPUS (Sudah tidak pakai)

        'nik',
        'company_name',
        'status',
        'gender',
        'profile_photo_path',
        'phone_number',
        'hiring_date',
        
        // --- DATA PRIBADI ---
        'place_of_birth',
        'date_of_birth',
        'domicile',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==========================================
    // 1. RELASI UTAMA (CORE RELATIONSHIPS)
    // ==========================================

    public function roles()
    {
        // Parameter: Model Tujuan, Nama Tabel Pivot, FK User, FK Role
        return $this->belongsToMany(\App\Models\Role::class, 'role_user', 'user_id', 'role_id');
    }

    /**
     * Relasi ke Tabel Positions (Jabatan)
     * Ini adalah pusat informasi struktur (Unit & Atasan ada di sini)
     */
    public function position(): BelongsTo 
    {
        return $this->belongsTo(Position::class);
    }

    // ==========================================
    // 2. JALAN PINTAS (ACCESSORS) - PENTING UNTUK VIEW
    // ==========================================

    /**
     * Ambil Nama Unit Kerja secara otomatis via Position
     * Panggil di Blade: {{ $user->unit_name }}
     */
    public function getUnitNameAttribute()
    {
        // User -> Position -> Organization -> Name
        return $this->position?->organization?->name ?? '-';
    }

    /**
     * Ambil Nama Atasan Langsung secara otomatis via Position
     * Panggil di Blade: {{ $user->atasan_name }}
     */
    public function getAtasanNameAttribute()
    {
        // User -> Position Saya -> Position Boss -> Orang yg menjabat -> Name
        return $this->position?->atasan?->user?->name ?? '-';
    }

    /**
     * Ambil Jabatan Atasan
     * Panggil di Blade: {{ $user->atasan_jabatan }}
     */
    public function getAtasanJabatanAttribute()
    {
        return $this->position?->atasan?->title ?? '-';
    }

    // ==========================================
    // 3. RELASI LAINNYA (PROFILE & HISTORY)
    // ==========================================

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