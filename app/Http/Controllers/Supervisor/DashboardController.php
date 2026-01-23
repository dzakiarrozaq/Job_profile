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
use App\Models\User; // Pastikan User di-import

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $supervisorPositionId = $user->position_id;

        // --- PERBAIKAN UTAMA DI SINI ---
        // Kita cari ID bawahan berdasarkan struktur Position (atasan_id)
        // Bukan lagi pakai 'manager_id' di tabel users
        $teamMemberIds = User::whereHas('position', function($query) use ($supervisorPositionId) {
            $query->where('atasan_id', $supervisorPositionId);
        })->pluck('id');

        // 1. Hitung Penilaian Kompetensi Pending
        $penilaianCount = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->distinct('user_id')
            ->count('user_id');

        // 2. Hitung Rencana Pelatihan Pending
        $rencanaCount = TrainingPlan::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_supervisor')
            ->count();

        // 3. Hitung Sertifikat Pending
        $sertifikatCount = TrainingEvidences::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending')
            ->count();
            
        // 4. Hitung IDP Pending
        $idpCount = Idp::whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->count();

        // 5. Hitung Job Profile Pending (Khusus ini logic-nya beda, via Position)
        $childPositionIds = Position::where('atasan_id', $user->position_id)->pluck('id');
        
        $pendingJobProfiles = JobProfile::whereIn('position_id', $childPositionIds)
            ->where('status', 'pending_verification')
            ->with('position', 'creator')
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

        // --- AMBIL DATA TUGAS (MENGGUNAKAN $teamMemberIds YANG SUDAH DIPERBAIKI) ---

        $tugasPenilaian = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user')
            ->get()
            ->unique('user_id')
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name,
                    'tipe' => 'PENILAIAN KOMPETENSI',
                    'status_sort' => '1_penilaian',
                    'tanggal' => $item->submitted_at,
                    'url' => route('supervisor.penilaian.show', $item->user_id)
                ];
            });

        $tugasRencana = TrainingPlan::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_supervisor')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name,
                    'tipe' => 'RENCANA PELATIHAN',
                    'status_sort' => '2_rencana', 
                    'tanggal' => $item->submitted_at,
                    'url' => route('supervisor.persetujuan') 
                ];
            });

        $tugasIdp = Idp::whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name,
                    'tipe' => 'IDP APPROVAL', 
                    'status_sort' => '3_idp', 
                    'tanggal' => $item->updated_at,
                    'url' => route('supervisor.idp.show', $item->id) 
                ];
            });

        $tugasSertifikat = TrainingEvidences::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name,
                    'tipe' => 'BUKTI SERTIFIKAT',
                    'status_sort' => '4_sertifikat', 
                    'tanggal' => $item->created_at,
                    'url' => route('supervisor.persetujuan') 
                ];
            });

        // Gabungkan Semua Tugas
        $tugasMendesak = $pendingJobProfiles
            ->concat($tugasPenilaian)
            ->concat($tugasRencana)
            ->concat($tugasIdp)        
            ->concat($tugasSertifikat)
            ->sortByDesc('tanggal');  

        // Ambil Data User Bawahan (Untuk daftar tim di dashboard)
        $teamMembers = User::whereIn('id', $teamMemberIds)->get();
        
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