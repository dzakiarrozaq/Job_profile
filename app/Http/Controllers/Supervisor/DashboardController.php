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
        
        // 1. Ambil ID bawahan langsung (Berdasarkan manager_id)
        $teamMemberIds = \App\Models\User::where('manager_id', $user->id)->pluck('id');

        // -------------------------------------------------------------
        // A. MENGHITUNG JUMLAH (COUNTING) UNTUK KARTU ATAS
        // -------------------------------------------------------------

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
            
        // Hitung IDP Pending (BARU)
        $idpCount = Idp::whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->count();

        // -------------------------------------------------------------
        // B. MENGAMBIL DATA TUGAS MENDESAK (TABEL)
        // -------------------------------------------------------------

        // 1. Job Profile Pending
        $childPositionIds = Position::where('atasan_id', $user->position_id)->pluck('id');
        
        $pendingJobProfiles = JobProfile::whereIn('position_id', $childPositionIds)
            ->where('status', 'pending_verification')
            ->with('position', 'creator')
            ->get()
            ->map(function ($profile) {
                return (object) [
                    'karyawan' => 'Posisi: ' . ($profile->position->title ?? 'Unknown'), 
                    'tipe' => 'USULAN JOB PROFILE',
                    'status_sort' => '0_jobprofile', // Prioritas 0
                    'tanggal' => $profile->updated_at,
                    'url' => route('supervisor.job-profile.edit', $profile->id),
                ];
            });

        // 2. Penilaian Kompetensi Pending
        $tugasPenilaian = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user')
            ->get()
            ->unique('user_id')
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name,
                    'tipe' => 'PENILAIAN KOMPETENSI',
                    'status_sort' => '1_penilaian', // Prioritas 1
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
                    'status_sort' => '2_rencana', // Prioritas 2
                    'tanggal' => $item->submitted_at,
                    // Arahkan ke tab 'catalog' di halaman persetujuan
                    'url' => route('supervisor.persetujuan') 
                ];
            });

        // 4. IDP Pending (BARU)
        $tugasIdp = Idp::whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name,
                    'tipe' => 'IDP APPROVAL', // Label di tabel
                    'status_sort' => '3_idp', // Prioritas 3 (Untuk styling warna ungu di view)
                    'tanggal' => $item->updated_at,
                    'url' => route('supervisor.idp.show', $item->id) // Link langsung ke detail review
                ];
            });

        // 5. Sertifikat Pending
        $tugasSertifikat = TrainingEvidences::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending')
            ->with('user')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'karyawan' => $item->user->name,
                    'tipe' => 'BUKTI SERTIFIKAT',
                    'status_sort' => '4_sertifikat', // Prioritas 4
                    'tanggal' => $item->created_at,
                    'url' => route('supervisor.persetujuan') 
                ];
            });

        // -------------------------------------------------------------
        // C. MERGE & SORTING SEMUA TUGAS
        // -------------------------------------------------------------
        $tugasMendesak = $pendingJobProfiles
            ->concat($tugasPenilaian)
            ->concat($tugasRencana)
            ->concat($tugasIdp)        // <--- Masukkan IDP ke sini
            ->concat($tugasSertifikat)
            ->sortByDesc('tanggal');   // Sort terbaru di atas

        // -------------------------------------------------------------
        // D. DATA ANGGOTA TIM
        // -------------------------------------------------------------
        // Gunakan relasi manual jika 'subordinates' belum didefinisikan di User model
        $teamMembers = \App\Models\User::where('manager_id', $user->id)->with('position')->get();

        return view('supervisor.dashboard', [
            'penilaianCount' => $penilaianCount,
            'rencanaCount'   => $rencanaCount,
            'sertifikatCount'=> $sertifikatCount,
            'jobProfileCount'=> $pendingJobProfiles->count(),
            'idpCount'       => $idpCount,       // <--- Kirim variable ini ke View
            'tugasMendesak'  => $tugasMendesak,
            'teamMembers'    => $teamMembers
        ]);
    }
}