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
        
        $user->load('position.jobProfile', 'manager');

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

            $neededCompetencies = $gapAnalysisData->where('gap_value', '<', 0)->pluck('competency_code');
            if ($neededCompetencies->isNotEmpty()) {
                 $recommendations = Training::where(function ($query) use ($neededCompetencies) {
                    foreach ($neededCompetencies as $code) {
                        $query->orWhere('skill_tags', 'LIKE', "%{$code}%");
                    }
                })
                ->where('status', 'approved')
                ->take(3)
                ->get();
            }
            
            $assessmentStatus = $user->employeeProfiles()
                                     ->where('status', 'pending_verification')
                                     ->exists();
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
        ]);
    }
}