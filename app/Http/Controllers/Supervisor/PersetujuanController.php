<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeProfile;
use App\Models\Training;
use App\Models\JobProfile;
use App\Models\Position; // Pastikan Import ini ada

class PersetujuanController extends Controller
{
    public function index(): View
    {
        $supervisor = Auth::user();
        $teamMemberIds = $supervisor->subordinates()->pluck('id');

        $pendingAssessments = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user.position')
            ->select('user_id', 'submitted_at')
            ->distinct()
            ->get()
            ->unique('user_id');

        $pendingTrainings = Training::where('status', 'pending_supervisor')
            ->whereIn('created_by', $teamMemberIds)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
        
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
            'trainings' => $pendingTrainings,    
            'jobProfiles' => $pendingJobProfiles, 
        ]);
    }
}