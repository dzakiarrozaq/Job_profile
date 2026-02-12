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
use App\Models\Idp;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(): View
    {        
        $user = Auth::user();
        
        $allSubordinatePositionIds = $user->position ? $user->position->getAllSubordinateIds() : [];
        $teamMemberIds = User::whereIn('position_id', $allSubordinatePositionIds)->pluck('id')->toArray();

        $pendingJobProfiles = JobProfile::whereIn('position_id', $allSubordinatePositionIds)
            ->where('status', 'pending_verification')
            ->with('position')
            ->get()
            ->map(function ($profile) {
                return (object) [
                    'karyawan'    => 'Posisi: ' . ($profile->position->title ?? 'Unknown'),
                    'user_id'     => null, 
                    'tipe'        => 'USULAN JOB PROFILE',
                    'status_sort' => '0_jobprofile', 
                    'tanggal'     => $profile->updated_at,
                    'url'         => route('supervisor.job-profile.edit', $profile->id),
                ];
            });

        $tugasPenilaian = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan'    => $item->user->name ?? 'User',
                    'user_id'     => $item->user_id,
                    'tipe'        => 'PENILAIAN KOMPETENSI',
                    'status_sort' => '1_penilaian',
                    'tanggal'     => $item->submitted_at,
                    'url'         => route('supervisor.penilaian.show', $item->user_id)
                ];
            });

        $tugasRencana = TrainingPlan::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_supervisor')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan'    => $item->user->name ?? 'User',
                    'user_id'     => $item->user_id, 
                    'tipe'        => 'RENCANA PELATIHAN',
                    'status_sort' => '2_rencana', 
                    'tanggal'     => $item->created_at,
                    'url'         => route('supervisor.persetujuan') 
                ];
            });

        $tugasIdp = Idp::whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan'    => $item->user->name ?? 'User',
                    'user_id'     => $item->user_id,
                    'tipe'        => 'IDP APPROVAL', 
                    'status_sort' => '3_idp', 
                    'tanggal'     => $item->updated_at,
                    'url'         => route('supervisor.idp.show', $item->id) 
                ];
            });

        $tugasSertifikat = TrainingEvidences::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan'    => $item->user->name ?? 'User',
                    'user_id'     => $item->user_id,
                    'tipe'        => 'BUKTI SERTIFIKAT',
                    'status_sort' => '4_sertifikat', 
                    'tanggal'     => $item->created_at,
                    'url'         => route('supervisor.persetujuan') 
                ];
            });
        
        $rencanaCount   = $tugasRencana->unique('user_id')->count();
        $penilaianCount = $tugasPenilaian->unique('user_id')->count();
        
        $sertifikatCount = $tugasSertifikat->count();
        $idpCount        = $tugasIdp->count();
        $jobProfileCount = $pendingJobProfiles->count();
        
        $tugasMendesak = $pendingJobProfiles
            ->concat($tugasPenilaian)
            ->concat($tugasRencana)
            ->concat($tugasIdp)        
            ->concat($tugasSertifikat)
            ->sortByDesc('tanggal');  

        $teamMembers = User::whereIn('id', $teamMemberIds)->with('position')->get();
        
        return view('supervisor.dashboard', [
            'penilaianCount'        => $penilaianCount,
            'jumlahRencanaPelatihan'=> $rencanaCount, 
            'sertifikatCount'       => $sertifikatCount,
            'jobProfileCount'       => $jobProfileCount,
            'idpCount'              => $idpCount,      
            'tugasMendesak'         => $tugasMendesak,
            'teamMembers'           => $teamMembers
        ]);
    }
}