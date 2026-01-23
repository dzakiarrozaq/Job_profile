<?php

namespace App\Http\Controllers\KaryawanOrganik;

use App\Http\Controllers\Controller;
use App\Models\GapRecord;
use App\Models\Training;
use App\Models\TrainingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Load relasi yang dibutuhkan untuk dashboard
        $user->load([
            'position.jobProfile.competencies',
            'position.organization',
            // 'manager',
            'gapRecords',
            'employeeProfile'
        ]);
        
        // Ambil status profil karyawan
        $employeeProfile = $user->employeeProfile; 
        
        if (!$employeeProfile) {
            $globalStatus = 'not_started';
        } else {
            $globalStatus = $employeeProfile->status;
        }

        // Ambil Job Profile user saat ini
        $jobProfile = $user->position?->jobProfile;
        
        // Inisialisasi variabel default
        $gapAnalysisData = collect([]);
        $recommendations = collect([]);
        $totalCompetencies = 0;
        $metCompetencies = 0;
        $gapCompetencies = 0;
        $assessmentStatus = false;

        // Jika user punya Job Profile (artinya punya standar kompetensi)
        if ($jobProfile) {
            // Ambil data GAP record user tersebut
            $gapAnalysisData = GapRecord::where('user_id', $user->id)
                ->where('job_profile_id', $jobProfile->id)
                ->orderBy('gap_value', 'asc')
                ->get();
            
            // Hitung statistik kompetensi
            $totalCompetencies = $gapAnalysisData->count();
            $gapCompetencies = $gapAnalysisData->where('gap_value', '<', 0)->count();
            $metCompetencies = $totalCompetencies - $gapCompetencies;

            // Ambil daftar nama kompetensi yang masih KURANG (Gap < 0)
            $neededCompetencies = $gapAnalysisData->where('gap_value', '<', 0)->pluck('competency_name');
            
            // --- BAGIAN LOGIKA REKOMENDASI PELATIHAN ---
            if ($neededCompetencies->isNotEmpty()) {
                $recommendations = Training::where(function ($query) use ($neededCompetencies) {
                    foreach ($neededCompetencies as $name) {
                        // Cari pelatihan yang mengandung nama kompetensi tersebut
                        // Cari di Title, Objective, Content, dan Competency Name
                        $query->orWhere('title', 'LIKE', "%{$name}%")
                              ->orWhere('objective', 'LIKE', "%{$name}%")     // Pengganti description
                              ->orWhere('content', 'LIKE', "%{$name}%")       // Pengganti description
                              ->orWhere('competency_name', 'LIKE', "%{$name}%"); 
                    }
                })
                ->take(3)
                ->get();
            } else {
                // Jika tidak ada gap (kompeten semua), tampilkan pelatihan terbaru saja
                $recommendations = Training::latest()->take(3)->get();
            }

            $assessmentStatus = ($globalStatus === 'pending_verification');
        }

        // Ambil riwayat rencana pelatihan terbaru (5 item terakhir)
        $recentTrainings = TrainingPlan::where('user_id', $user->id)
            ->with('items.training')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('karyawan.dashboard', [
            'user' => $user, 
            'gapAnalysisData' => $gapAnalysisData,
            'totalCompetencies' => $totalCompetencies,
            'metCompetencies' => $metCompetencies,
            'gapCompetencies' => $gapCompetencies,
            'recommendations' => $recommendations,
            'recentTrainings' => $recentTrainings,
            'assessmentStatus' => $assessmentStatus,
            'globalStatus' => $globalStatus,
        ]);
    }

    public function riwayat(Request $request)
    {
        $user = Auth::user();

        $query = TrainingPlan::where('user_id', $user->id)
                            ->with('items.training');

        // Filter Status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Filter Tahun
        if ($request->has('year') && $request->year != 'all') {
            $query->whereYear('created_at', $request->year);
        }

        $trainingHistory = $query->orderBy('created_at', 'desc')->get();

        return view('karyawan.riwayat', [
            'trainingHistory' => $trainingHistory,
            'filters' => $request->all()
        ]);
    }
}