<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeProfile;
use App\Models\TrainingPlan;
use App\Models\JobProfile;
use App\Models\Position; 
use App\Models\Idp; // Jangan lupa use Model IDP

class PersetujuanController extends Controller
{
    public function index(): View
    {
        $supervisor = Auth::user();
        
        $teamMemberIds = $supervisor->subordinates()->pluck('id');

        $assessments = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user.position')
            ->select('user_id', 'submitted_at')
            ->distinct()
            ->get()
            ->unique('user_id');

        
        $trainings = TrainingPlan::where('status', 'pending_supervisor')
            ->whereIn('user_id', $teamMemberIds) 
            ->with(['user', 'items.training']) 
            ->orderBy('created_at', 'desc')
            ->get();
        
        $supervisorPositionId = $supervisor->position_id;
        $childPositionIds = Position::where('atasan_id', $supervisorPositionId)
                                    ->pluck('id');

        $jobProfiles = JobProfile::whereIn('position_id', $childPositionIds)
            ->where('status', 'pending_verification')
            ->with('position', 'creator')
            ->orderBy('updated_at', 'desc')
            ->get();

        $pendingIdps = Idp::with('user') // Pastikan 'use App\Models\Idp' di atas
            ->whereHas('user', function($q) {
                $q->where('manager_id', auth()->id());
            })
            ->where('status', 'submitted')
            ->latest()
            ->get();

        return view('supervisor.persetujuan.index', compact(
            'assessments', 
            'trainings', 
            'jobProfiles', 
            'pendingIdps' 
        ));
    }
}