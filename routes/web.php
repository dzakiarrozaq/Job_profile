<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Controllers\Supervisor\LaporanController;
use App\Http\Controllers\Admin\SystemReportController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\TrainingRecommendationController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\Admin\AdminTrainingController;
use App\Http\Controllers\IdpController;
use App\Http\Controllers\Supervisor\SupervisorIdpController;
use App\Http\Controllers\KaryawanOrganik\TrainingPlanController;
use App\Http\Controllers\Supervisor\PersetujuanController;
use App\Http\Controllers\Lp\LaporanController as LpLaporanController;
use App\Http\Controllers\Lp\TrainingController as LpTrainingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- Rute Umum ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/profile/keahlian', [ProfileController::class, 'editSkills'])->name('profile.skills.edit');
    Route::patch('/profile/keahlian', [ProfileController::class, 'updateSkills'])->name('profile.skills.update');
    Route::get('/profile/minat', [ProfileController::class, 'editInterests'])->name('profile.interests.edit');
    Route::patch('/profile/minat', [ProfileController::class, 'updateInterests'])->name('profile.interests.update');

    Route::post('/profile/trigger-reset-password', [ProfileController::class, 'triggerResetPassword'])
         ->name('profile.trigger-reset');
    
    // --- Rute Karyawan ---
    Route::middleware(['role:Karyawan Organik,Supervisor,Karyawan Outsourcing'])->group(function () {
        
        // PERBAIKAN: Menghapus 'uri:' yang menyebabkan error
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian');
        Route::post('/penilaian', [PenilaianController::class, 'store'])->name('penilaian.store');
        Route::get('/riwayat', [DashboardController::class, 'riwayat'])->name('riwayat');
        Route::get('/rekomendasi', [TrainingRecommendationController::class, 'index'])->name('rekomendasi');
        Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog');
        Route::get('/katalog/{id}', [KatalogController::class, 'show'])->name('katalog.show');

        Route::get('/my-idp', [IdpController::class, 'index'])->name('idp.index');
        Route::post('/my-idp', [IdpController::class, 'store'])->name('idp.store');

        Route::get('/rencana', [TrainingPlanController::class, 'index'])->name('rencana.index');
        Route::post('/rencana', [TrainingPlanController::class, 'store'])->name('rencana.store');

        Route::get('/rencana/{id}', [TrainingPlanController::class, 'show'])->name('rencana.show');
        Route::delete('/rencana/{id}', [TrainingPlanController::class, 'destroy'])->name('rencana.destroy');

        Route::get('/training/{id}', [TrainingPlanController::class, 'showTraining'])->name('training.show');  
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
        Route::get('/tim/create', [TeamController::class, 'create'])->name('tim.create'); 
        Route::post('/tim', [TeamController::class, 'store'])->name('tim.store');
        Route::get('/tim/{user}', [TeamController::class, 'show'])->name('tim.show');

        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

        Route::get('/idp-approval', [SupervisorIdpController::class, 'index'])->name('idp.index');
        Route::get('/idp-approval/{id}', [SupervisorIdpController::class, 'show'])->name('idp.show');
        Route::post('/idp-approval/{id}', [SupervisorIdpController::class, 'update'])->name('idp.update');

        Route::get('/rencana/{id}', [PersetujuanController::class, 'show'])->name('rencana.show');
        
        // 2. Route untuk Action Approve
        Route::post('/rencana/{id}/approve', [PersetujuanController::class, 'approve'])->name('approve');
        
        // 3. Route untuk Action Reject
        Route::post('/rencana/{id}/reject', [PersetujuanController::class, 'reject'])->name('reject');
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

        Route::resource('users', AdminUserController::class);

        Route::get('/laporan-sistem', [SystemReportController::class, 'index'])->name('laporan.index');
        Route::get('/laporan-sistem/export', [SystemReportController::class, 'export'])->name('laporan.admin.export'); 
        
        Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
        Route::get('/logs/export', [ActivityLogController::class, 'export'])->name('logs.export'); 

        Route::get('/trainings', [AdminTrainingController::class, 'index'])->name('trainings.index');

        Route::get('/trainings/create', [AdminTrainingController::class, 'create'])->name('trainings.create');
        Route::post('/trainings', [AdminTrainingController::class, 'store'])->name('trainings.store');
        
        Route::delete('/trainings/{id}', [AdminTrainingController::class, 'destroy'])->name('trainings.destroy');
        Route::get('/trainings/{id}/edit', [AdminTrainingController::class, 'edit'])->name('trainings.edit');
        Route::put('/trainings/{id}', [AdminTrainingController::class, 'update'])->name('trainings.update');
    });

    
    // --- Rute Learning Partner ---
    Route::middleware(['role:Learning Partner'])->prefix('lp')->name('lp.')->group(function () {
        Route::get('/dashboard', [LpDashboardController::class, 'index'])->name('dashboard');
        Route::get('/persetujuan', [LpPersetujuanController::class, 'index'])->name('persetujuan');
        Route::get('/laporan', [LpLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/persetujuan/{id}', [LpPersetujuanController::class, 'show'])->name('persetujuan.show');
        Route::post('/persetujuan/{id}/approve', [LpPersetujuanController::class, 'approve'])->name('persetujuan.approve');
        Route::post('/persetujuan/{id}/reject', [LpPersetujuanController::class, 'reject'])->name('persetujuan.reject');
        Route::resource('katalog', LpTrainingController::class);
    });

    Route::get('/notifications/mark-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back();
    })->name('notifications.markRead');

    Route::get('/pilih-peran', [App\Http\Controllers\Auth\RoleSelectionController::class, 'create'])->name('role.selection');
    Route::post('/pilih-peran', [App\Http\Controllers\Auth\RoleSelectionController::class, 'store'])->name('role.set');

});

require __DIR__.'/auth.php';