<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// KONTROLER KARYAWAN
use App\Http\Controllers\KaryawanOrganik\DashboardController;
use App\Http\Controllers\KaryawanOrganik\PenilaianController;

// KONTROLER DARI SUB-FOLDER
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\JobProfileController; 
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Supervisor\PersetujuanController as SupervisorPersetujuanController;
use App\Http\Controllers\Supervisor\VerifikasiKompetensiController;
use App\Http\Controllers\Lp\DashboardController as LpDashboardController;
use App\Http\Controllers\Lp\PersetujuanController as LpPersetujuanController;
use App\Http\Controllers\Auth\RoleSelectionController;
use App\Http\Controllers\Supervisor\TeamController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- Rute Umum (Semua role bisa akses) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/profile/keahlian', [ProfileController::class, 'editSkills'])->name('profile.skills.edit');
    Route::patch('/profile/keahlian', [ProfileController::class, 'updateSkills'])->name('profile.skills.update');
    Route::get('/profile/minat', [ProfileController::class, 'editInterests'])->name('profile.interests.edit');
    Route::patch('/profile/minat', [ProfileController::class, 'updateInterests'])->name('profile.interests.update');
    
    // --- Rute Karyawan ---
    Route::middleware(['role:Karyawan Organik,Supervisor'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian');
        Route::post('/penilaian', [PenilaianController::class, 'store'])->name('penilaian.store');
        Route::get('/katalog', function () { return view('karyawan.katalog'); })->name('katalog');
        Route::get('/riwayat', [DashboardController::class, 'riwayat'])->name('riwayat');
        Route::get('/rencana', function () { return view('karyawan.rencana'); })->name('rencana');
        
    });

    
    // --- Rute Supervisor ---
    Route::middleware(['role:Supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/persetujuan', [SupervisorPersetujuanController::class, 'index'])->name('persetujuan');
        Route::get('/verifikasi-kompetensi/{user}', [VerifikasiKompetensiController::class, 'show'])->name('penilaian.show');
        Route::post('/verifikasi-kompetensi/{user}', [VerifikasiKompetensiController::class, 'store'])->name('penilaian.store');
        
        Route::get('/profile', function () { return view('supervisor.profile', ['user' => Auth::user()]); })->name('profile');
        Route::patch('/profile', [ProfileController::class, 'updateSupervisorProfile'])->name('profile.update');
        
        Route::get('/job-profile', [JobProfileController::class, 'index'])->name('job-profile.index');
        Route::get('/job-profile/create', [JobProfileController::class, 'create'])->name('job-profile.create');
        Route::post('/job-profile', [JobProfileController::class, 'store'])->name('job-profile.store');
        Route::get('/job-profile/{job_profile}/edit', [JobProfileController::class, 'edit'])->name('job-profile.edit');
        Route::patch('/job-profile/{job_profile}', [JobProfileController::class, 'update'])->name('job-profile.update');
        Route::delete('/job-profile/{job_profile}', [JobProfileController::class, 'destroy'])->name('job-profile.destroy');
        
        Route::post('/job-profile/suggest-text', [JobProfileController::class, 'suggestText'])->name('job-profile.suggestText');
        Route::get('/competencies/search', [App\Http\Controllers\Admin\JobProfileController::class, 'searchCompetencies'])->name('competencies.search');

        Route::get('/tim', [TeamController::class, 'index'])->name('tim.index');
        Route::get('/tim/create', [TeamController::class, 'create'])->name('tim.create'); // <-- WAJIB DI ATAS
        Route::post('/tim', [TeamController::class, 'store'])->name('tim.store');
        Route::get('/tim/{user}', [TeamController::class, 'show'])->name('tim.show');
    });

    
    // --- Rute Admin ---
    Route::middleware(['role:Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/manajemen-user', [AdminUserController::class, 'index'])->name('users.index');
        Route::resource('positions', PositionController::class);
        
        Route::get('/job-profile', [JobProfileController::class, 'index'])->name('job-profile.index');
        Route::get('/job-profile/create', [JobProfileController::class, 'create'])->name('job-profile.create');
        Route::post('/job-profile', [JobProfileController::class, 'store'])->name('job-profile.store');
        Route::get('/job-profile/{job_profile}/edit', [JobProfileController::class, 'edit'])->name('job-profile.edit');
        Route::patch('/job-profile/{job_profile}', [JobProfileController::class, 'update'])->name('job-profile.update');
        Route::delete('/job-profile/{job_profile}', [JobProfileController::class, 'destroy'])->name('job-profile.destroy');

        Route::post('/job-profile/suggest-text', [JobProfileController::class, 'suggestText'])->name('job-profile.suggestText');
        Route::get('/job-profile/search-competencies', [JobProfileController::class, 'searchCompetencies'])->name('competencies.search');
    });

    
    // --- Rute Learning Partner ---
    Route::middleware(['role:Learning Partner'])->prefix('lp')->name('lp.')->group(function () {
        Route::get('/dashboard', [LpDashboardController::class, 'index'])->name('dashboard');
        Route::get('/persetujuan', [LpPersetujuanController::class, 'index'])->name('persetujuan');
    });

    Route::get('/notifications/mark-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back();
    })->name('notifications.markRead');

    Route::get('/pilih-peran', [App\Http\Controllers\Auth\RoleSelectionController::class, 'create'])->name('role.selection');
    Route::post('/pilih-peran', [App\Http\Controllers\Auth\RoleSelectionController::class, 'store'])->name('role.set');

});


// File auth bawaan Laravel
require __DIR__.'/auth.php';