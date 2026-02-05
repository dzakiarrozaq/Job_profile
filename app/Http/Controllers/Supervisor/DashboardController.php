<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeProfile;
use App\Models\TrainingPlan;
use App\Models\TrainingEvidences;
use App\Models\JobProfile;
use App\Models\Position;
use App\Models\Idp;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        
        // 1. Ambil semua ID posisi di bawah hirarki User (Recursive)
        // Kita gunakan fungsi yang sudah dibuat di model Position
        $allSubordinatePositionIds = $user->position ? $user->position->getAllSubordinateIds() : [];

        // 2. Ambil semua ID User yang menduduki posisi-posisi tersebut
        $teamMemberIds = User::whereIn('position_id', $allSubordinatePositionIds)->pluck('id');

        // --- HITUNG STATISTIK (MENGGUNAKAN SELURUH HIRARKI TIM) ---

        // 1. Penilaian Kompetensi Pending
        $penilaianCount = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->count();

        // 2. Rencana Pelatihan Pending
        $rencanaCount = TrainingPlan::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_supervisor')
            ->count();

        // 3. Sertifikat Pending
        $sertifikatCount = TrainingEvidences::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending')
            ->count();
            
        // 4. IDP Pending
        $idpCount = Idp::whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->count();

        // --- AMBIL DATA TUGAS MENDESAK (RECURSIVE) ---

        // 5. Job Profile Pending (Berdasarkan Hirarki Posisi)
        $pendingJobProfiles = JobProfile::whereIn('position_id', $allSubordinatePositionIds)
            ->where('status', 'pending_verification')
            ->with('position')
            ->get()
            ->map(function ($profile) {
                return (object) [
                    'karyawan' => 'Posisi: ' . ($profile->position->title ?? 'Unknown'), 
                    'tipe' => 'USULAN JOB PROFILE',
                    'status_sort' => '0_jobprofile', 
                    'tanggal' => $profile->updated_at,
                    'url' => route('supervisor.job-profile.edit', $profile->id),
                ];
            });

        // 6. Penilaian Kompetensi
        $tugasPenilaian = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name ?? 'User',
                    'tipe' => 'PENILAIAN KOMPETENSI',
                    'status_sort' => '1_penilaian',
                    'tanggal' => $item->submitted_at,
                    'url' => route('supervisor.penilaian.show', $item->user_id)
                ];
            });

        // 7. Rencana Pelatihan
        $tugasRencana = TrainingPlan::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_supervisor')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name ?? 'User',
                    'tipe' => 'RENCANA PELATIHAN',
                    'status_sort' => '2_rencana', 
                    'tanggal' => $item->created_at,
                    'url' => route('supervisor.persetujuan') 
                ];
            });

        // 8. IDP Approval
        $tugasIdp = Idp::whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name ?? 'User',
                    'tipe' => 'IDP APPROVAL', 
                    'status_sort' => '3_idp', 
                    'tanggal' => $item->updated_at,
                    'url' => route('supervisor.idp.show', $item->id) 
                ];
            });

        // 9. Sertifikat
        $tugasSertifikat = TrainingEvidences::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name ?? 'User',
                    'tipe' => 'BUKTI SERTIFIKAT',
                    'status_sort' => '4_sertifikat', 
                    'tanggal' => $item->created_at,
                    'url' => route('supervisor.persetujuan') 
                ];
            });

        // Gabungkan Semua Tugas & Urutkan berdasarkan tanggal terbaru
        $tugasMendesak = $pendingJobProfiles
            ->concat($tugasPenilaian)
            ->concat($tugasRencana)
            ->concat($tugasIdp)        
            ->concat($tugasSertifikat)
            ->sortByDesc('tanggal');  

        // Ambil Data User Bawahan untuk list di Dashboard
        $teamMembers = User::whereIn('id', $teamMemberIds)->with('position')->get();
        
        return view('supervisor.dashboard', [
            'penilaianCount' => $penilaianCount,
            'rencanaCount'   => $rencanaCount,
            'sertifikatCount'=> $sertifikatCount,
            'jobProfileCount'=> $pendingJobProfiles->count(),
            'idpCount'       => $idpCount,      
            'tugasMendesak'  => $tugasMendesak,
            'teamMembers'    => $teamMembers
        ]);
    }
}