<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeProfile;
use App\Models\TrainingPlan; // Pastikan Model ini di-use
use App\Models\JobProfile;
use App\Models\Position; 

class PersetujuanController extends Controller
{
    public function index(): View
    {
        $supervisor = Auth::user();
        
        // 1. Ambil ID semua bawahan
        $teamMemberIds = $supervisor->subordinates()->pluck('id');

        // 2. Ambil Penilaian Kompetensi yang Pending
        $pendingAssessments = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user.position')
            ->select('user_id', 'submitted_at')
            ->distinct()
            ->get()
            ->unique('user_id');

        // 3. Ambil Rencana Pelatihan (Training Plan) yang Pending
        // PERBAIKAN: Gunakan $teamMemberIds (bukan $teamIds)
        // PERBAIKAN: Gunakan model TrainingPlan (bukan Training)
        $trainingApprovals = TrainingPlan::where('status', 'pending_supervisor')
            ->whereIn('user_id', $teamMemberIds) 
            ->with(['user', 'items.training']) // Load relasi user & detail pelatihan
            ->orderBy('created_at', 'desc')
            ->get();
        
        // 4. Ambil Job Profile yang Pending (Logic Job Profile)
        $supervisorPositionId = $supervisor->position_id;
        $childPositionIds = Position::where('atasan_id', $supervisorPositionId)
                                    ->pluck('id');

        $pendingJobProfiles = JobProfile::whereIn('position_id', $childPositionIds)
            ->where('status', 'pending_verification')
            ->with('position', 'creator')
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return view('supervisor.persetujuan.index', [
            'assessments' => $pendingAssessments,
            'trainings'   => $trainingApprovals, // Masukkan data training plan yang benar ke sini
            'jobProfiles' => $pendingJobProfiles, 
        ]);
    }
}