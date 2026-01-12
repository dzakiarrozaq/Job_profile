<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeProfile;
use App\Models\TrainingPlan;
use App\Models\TrainingEvidences; // Pastikan nama Model benar (plural/singular)
use App\Models\JobProfile; 
use App\Models\Position;
use App\Models\Idp; // <--- Tambahkan Model IDP

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        
        $teamMemberIds = \App\Models\User::where('manager_id', $user->id)->pluck('id');

       
        $penilaianCount = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->distinct('user_id')
            ->count('user_id');

        $rencanaCount = TrainingPlan::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_supervisor')
            ->count();

        $sertifikatCount = TrainingEvidences::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending')
            ->count();
            
        $idpCount = Idp::whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->count();


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

        // 3. Rencana Pelatihan Pending
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

        
        $tugasMendesak = $pendingJobProfiles
            ->concat($tugasPenilaian)
            ->concat($tugasRencana)
            ->concat($tugasIdp)        
            ->concat($tugasSertifikat)
            ->sortByDesc('tanggal');  

        
        $teamMembers = \App\Models\User::where('manager_id', $user->id)->with('position')->get();

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