<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\KaryawanOrganik\DashboardController;
use App\Http\Controllers\KaryawanOrganik\PenilaianController;

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
use App\Http\Controllers\Lp\ProfileController as LpProfileController;
use App\Http\Controllers\Lp\TrainingController;
use App\Http\Controllers\Admin\PositionHierarchyController;
use App\Http\Controllers\Admin\CompetencyController;
use App\Models\JobGrade;
use App\Models\Position;

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

    Route::post('/profile/job-history', [ProfileController::class, 'storeJobHistory'])->name('profile.job-history.store');
    Route::post('/profile/education', [ProfileController::class, 'storeEducation'])->name('profile.education.store');
    
    Route::delete('/profile/job-history/{id}', [ProfileController::class, 'destroyJobHistory'])->name('profile.job-history.destroy');
    Route::delete('/profile/education/{id}', [ProfileController::class, 'destroyEducation'])->name('profile.education.destroy');

    Route::delete('/profile/job-history/{id}', [ProfileController::class, 'destroyJobHistory'])->name('profile.job-history.destroy');
    Route::delete('/profile/education/{id}', [ProfileController::class, 'destroyEducation'])->name('profile.education.destroy');

    Route::post('/profile/trigger-reset-password', [ProfileController::class, 'triggerResetPassword'])
         ->name('profile.trigger-reset');
    

    Route::get('/notifikasi/{id}/baca', [App\Http\Controllers\NotificationController::class, 'markAsReadAndRedirect'])->name('notifikasi.baca');
    Route::post('/notifikasi/baca-semua', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifikasi.bacaSemua');
    
    // --- Rute Karyawan ---
    Route::middleware(['role:Karyawan Organik,Supervisor,Karyawan Outsourcing'])->group(function () {
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian');
        Route::post('/penilaian', [PenilaianController::class, 'store'])->name('penilaian.store');
        Route::get('/riwayat', [DashboardController::class, 'riwayat'])->name('riwayat');
        Route::get('/rekomendasi', [TrainingRecommendationController::class, 'index'])->name('rekomendasi');
        Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog');
        Route::get('/katalog/{id}', [KatalogController::class, 'show'])->name('katalog.show');

        Route::get('/my-idp', [IdpController::class, 'index'])->name('idp.index');
        Route::post('/my-idp', [IdpController::class, 'store'])->name('idp.store');
        Route::get('/idp', [IdpController::class, 'index'])->name('idp.index');
        Route::get('/idp/create', [IdpController::class, 'create'])->name('idp.create');
        Route::post('/idp', [IdpController::class, 'store'])->name('idp.store');

        Route::get('/idp/{id}/edit', [IdpController::class, 'edit'])->name('idp.edit');
        Route::put('/idp/{id}', [IdpController::class, 'update'])->name('idp.update');
    
        Route::get('/idp/{id}', [IdpController::class, 'show'])->name('idp.show');

        Route::get('/rencana', [TrainingPlanController::class, 'index'])->name('rencana.index');
        Route::post('/rencana', [TrainingPlanController::class, 'store'])->name('rencana.store');

        Route::get('/rencana/{id}', [TrainingPlanController::class, 'show'])->name('rencana.show');
        Route::delete('/rencana/{id}', [TrainingPlanController::class, 'destroy'])->name('rencana.destroy');
        Route::post('/rencana/submit-all', [TrainingPlanController::class, 'submitAll'])->name('rencana.submitAll');

        Route::get('/training/{id}', [TrainingPlanController::class, 'showTraining'])->name('training.show');  

        Route::get('/rencana/sertifikat/{itemId}', [TrainingPlanController::class, 'formSertifikat'])->name('rencana.sertifikat');
        Route::put('/rencana/sertifikat/{itemId}', [TrainingPlanController::class, 'storeSertifikat'])->name('rencana.sertifikat.store');
    });

    // ==============================================================================
    // 1. GROUP KHUSUS SUPERVISOR (Prefix: /supervisor)
    // ==============================================================================
    Route::middleware(['auth', 'role:Supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
        
        // Dashboard & Menu Utama Supervisor
        Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/persetujuan', [SupervisorPersetujuanController::class, 'index'])->name('persetujuan');
        
        // Verifikasi Kompetensi & Sertifikat
        Route::get('/verifikasi-kompetensi/{user}', [VerifikasiKompetensiController::class, 'show'])->name('penilaian.show');
        Route::post('/verifikasi-kompetensi/{user}', [VerifikasiKompetensiController::class, 'store'])->name('penilaian.store');
        Route::get('/verifikasi-sertifikat', [App\Http\Controllers\Supervisor\CertificateController::class, 'index'])->name('sertifikat.index');
        Route::post('/verifikasi-sertifikat/{id}/approve', [App\Http\Controllers\Supervisor\CertificateController::class, 'approve'])->name('sertifikat.approve');
        Route::post('/verifikasi-sertifikat/{id}/reject', [App\Http\Controllers\Supervisor\CertificateController::class, 'reject'])->name('sertifikat.reject');
        
        // Profil Diri & Tim
        Route::get('/profile', function () { return view('supervisor.profile', ['user' => Auth::user()]); })->name('profile');
        Route::patch('/profile', [ProfileController::class, 'updateSupervisorProfile'])->name('profile.update');
        Route::resource('tim', TeamController::class); // Menggantikan manual get/post/create

        // Laporan & IDP
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
        Route::get('/idp-approval', [SupervisorIdpController::class, 'index'])->name('idp.index');
        Route::get('/idp-approval/{id}', [SupervisorIdpController::class, 'show'])->name('idp.show');
        Route::post('/idp-approval/{id}', [SupervisorIdpController::class, 'update'])->name('idp.update');

        // Group Supervisor
        Route::get('/persetujuan/idp/{id}', [PersetujuanController::class, 'showIdp'])->name('persetujuan.idp.show');
        Route::post('/persetujuan/idp/{id}/approve', [PersetujuanController::class, 'approveIdp'])->name('persetujuan.idp.approve');
        Route::post('/persetujuan/idp/{id}/reject', [PersetujuanController::class, 'rejectIdp'])->name('persetujuan.idp.reject');
        
        // Rencana Persetujuan Lainnya
        Route::get('/rencana/{id}', [PersetujuanController::class, 'show'])->name('rencana.show');
        Route::post('/rencana/{id}/approve', [PersetujuanController::class, 'approve'])->name('approve');
        Route::post('/rencana/{id}/reject', [PersetujuanController::class, 'reject'])->name('reject');

        // --------------------------------------------------------------------------
        // JOB PROFILE (Versi Supervisor)
        // URL: /supervisor/job-profile/...
        // Route Name: supervisor.job-profile.*
        // --------------------------------------------------------------------------
        Route::post('/job-profile/suggest-text', [JobProfileController::class, 'suggestText'])->name('job-profile.suggestText');
        Route::get('/job-profile/search-competencies', [JobProfileController::class, 'searchCompetencies'])->name('competencies.search');
        
        // Gunakan Resource agar otomatis membuat index, create, store, edit, update, destroy
        Route::resource('job-profile', JobProfileController::class); 

        // Route untuk Review Gabungan per User
        Route::get('/review-user/{userId}', [PersetujuanController::class, 'reviewByUser'])->name('review.user');
        
        // Route Aksi Approve/Reject Massal
        Route::post('/approve-user/{userId}', [PersetujuanController::class, 'approveByUser'])->name('approve.user');
        Route::post('/reject-user/{userId}', [PersetujuanController::class, 'rejectByUser'])->name('reject.user');

        Route::post('/item/{itemId}/approve', [PersetujuanController::class, 'approveItem'])->name('approve.item');
        Route::post('/item/{itemId}/reject', [PersetujuanController::class, 'rejectItem'])->name('reject.item');

        Route::post('/sertifikat/{id}/reject', [PersetujuanController::class, 'rejectSertifikat'])
        ->name('supervisor.sertifikat.reject');
    });


    // ==============================================================================
    // 2. GROUP KHUSUS ADMIN (Prefix: /admin)
    // ==============================================================================
    Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Posisi & Hirarki
        Route::get('/positions/hierarchy', [PositionHierarchyController::class, 'index'])->name('positions.hierarchy');
        Route::post('/positions/hierarchy/update', [PositionHierarchyController::class, 'updateParent'])->name('positions.updateHierarchy');
        Route::resource('positions', PositionController::class);

        // Training
        Route::post('/trainings/import', [AdminTrainingController::class, 'import'])->name('trainings.import');
        Route::resource('trainings', AdminTrainingController::class);

        // User Management
        Route::get('/manajemen-user', [AdminUserController::class, 'index'])->name('users.index');
        Route::resource('users', AdminUserController::class);
        Route::post('/users/import', [AdminUserController::class, 'import'])->name('users.import');
        Route::get('/users/template', [AdminUserController::class, 'downloadTemplate'])->name('users.template');
        

        // Laporan & Logs
        Route::get('/laporan-sistem', [SystemReportController::class, 'index'])->name('laporan.index');
        Route::get('/laporan-sistem/export', [SystemReportController::class, 'export'])->name('laporan.admin.export'); 
        Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
        Route::get('/logs/export', [ActivityLogController::class, 'export'])->name('logs.export'); 

        Route::post('/job-profile/suggest-text', [JobProfileController::class, 'suggestText'])->name('job-profile.suggestText');
        Route::get('/job-profile/search-competencies', [JobProfileController::class, 'searchCompetencies'])->name('competencies.search');
        
        // Resource Controller otomatis membuat semua route CRUD
        Route::resource('job-profile', JobProfileController::class);
        
        Route::get('/competencies', [App\Http\Controllers\Admin\CompetencyController::class, 'index'])->name('competencies.index');
        Route::post('/competencies/import', [App\Http\Controllers\Admin\CompetencyController::class, 'import'])->name('competencies.import');

        Route::get('/competencies/{id}/edit', [CompetencyController::class, 'edit'])->name('competencies.edit');
        Route::delete('/competencies/{id}', [CompetencyController::class, 'destroy'])->name('competencies.destroy');
        Route::put('/competencies/{id}', [CompetencyController::class, 'update'])->name('competencies.update'); // <--- PENTING: PUT

        Route::get('/api/get-responsibilities', [JobProfileController::class, 'getResponsibilitiesByBand'])->name('api.get-responsibilities');

        // Route untuk Import Master Tanggung Jawab
        Route::post('/master-responsibilities/import', [JobProfileController::class, 'importMasterResponsibilities'])
        ->name('master.import'); // Nama route jadi 'admin.master.import' otomatis karena group name('admin.')

        Route::post('/competencies/import-definition', [App\Http\Controllers\Admin\CompetencyController::class, 'importBehaviorDefinition'])
        ->name('competencies.import.definition');

        // 3. Route Import PERILAKU MATRIX (Untuk tombol ke-2 nanti)
        // Tambahkan juga biar tidak error nanti:
        Route::post('/competencies/import-behavior', [App\Http\Controllers\Admin\CompetencyController::class, 'importBehaviorMatrix'])
            ->name('competencies.import.behavior');

        Route::post('/competencies/import-technical-standard', [JobProfileController::class, 'importTechnicalStandard'])
            ->name('competencies.import.technical-standard');
    });

    
    // --- Rute Learning Partner ---
    Route::middleware(['auth', 'verified', 'role:Learning Partner'])->prefix('lp')->name('lp.')->group(function () {
    
        // --- DASHBOARD ---
        Route::get('/dashboard', [LpDashboardController::class, 'index'])->name('dashboard');

        // --- PERSETUJUAN (VERIFIKASI) ---
        // 1. Index (Daftar User)
        Route::get('/persetujuan', [LpPersetujuanController::class, 'index'])
            ->name('persetujuan.index'); 

        // 2. Review Detail per User (Gunakan nama beda: review-user)
        Route::get('/persetujuan/user/{userId}', [LpPersetujuanController::class, 'reviewByUser'])
            ->name('persetujuan.review-user');

        // 3. Review Detail per Plan (Opsional/Fallback)
        Route::get('/persetujuan/{id}', [LpPersetujuanController::class, 'show'])
            ->name('persetujuan.show');

        // 4. Action Approve & Reject
        Route::post('/persetujuan/{id}/approve', [LpPersetujuanController::class, 'approve'])
            ->name('persetujuan.approve');
        Route::post('/persetujuan/{id}/reject', [LpPersetujuanController::class, 'reject'])
            ->name('persetujuan.reject');

        // --- LAPORAN ---
        Route::get('/laporan', [LpLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export', [LpLaporanController::class, 'export'])->name('laporan.export');
        Route::get('/laporan/{id}', [LpLaporanController::class, 'show'])->name('laporan.show');

        // --- KATALOG TRAINING ---
        // Import diletakkan SEBELUM resource agar tidak dianggap sebagai {id}
        Route::post('/katalog/import', [LpTrainingController::class, 'import'])->name('katalog.import');
        Route::resource('katalog', LpTrainingController::class);

        // --- PROFILE ---
        Route::get('/profile', [LpProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [LpProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [LpProfileController::class, 'updatePassword'])->name('password.update');
    });

    Route::get('/notifications/mark-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back();
    })->name('notifications.markRead');

    Route::get('/pilih-peran', [App\Http\Controllers\Auth\RoleSelectionController::class, 'index'])->name('role.selection');
    Route::post('/pilih-peran', [App\Http\Controllers\Auth\RoleSelectionController::class, 'select'])->name('role.select');

   
});


require __DIR__.'/auth.php';