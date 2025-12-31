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

        
        $user->load([
            'position.jobProfile.competencies',
            'position.unit',
            'manager',
            'gapRecords',
            'employeeProfile'
        ]);
        
        
        
        $employeeProfile = $user->employeeProfile; 
        
        if (!$employeeProfile) {
            $globalStatus = 'not_started';
        } else {
            $globalStatus = $employeeProfile->status;
        }

    
        $jobProfile = $user->position?->jobProfile;
        $gapAnalysisData = collect([]);
        $recommendations = collect([]);
        $totalCompetencies = 0;
        $metCompetencies = 0;
        $gapCompetencies = 0;
        $assessmentStatus = false;

        if ($jobProfile) {
            $gapAnalysisData = GapRecord::where('user_id', $user->id)
                ->where('job_profile_id', $jobProfile->id)
                ->orderBy('gap_value', 'asc')
                ->get();
            
            $totalCompetencies = $gapAnalysisData->count();
            $gapCompetencies = $gapAnalysisData->where('gap_value', '<', 0)->count();
            $metCompetencies = $totalCompetencies - $gapCompetencies;

           
           
           
            $neededCompetencies = $gapAnalysisData->where('gap_value', '<', 0)->pluck('competency_name');
            
            if ($neededCompetencies->isNotEmpty()) {
                $recommendations = Training::where(function ($query) use ($neededCompetencies) {
                    foreach ($neededCompetencies as $name) {
                        $query->orWhere('title', 'LIKE', "%{$name}%")
                              ->orWhere('description', 'LIKE', "%{$name}%");
                    }
                })
                ->take(3)
                ->get();
            } else {
                $recommendations = Training::latest()->take(3)->get();
            }

            $assessmentStatus = ($globalStatus === 'pending_verification');
        }

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

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

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