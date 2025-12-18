<?php

namespace App\Http\Controllers\Lp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Training;
use App\Models\TrainingPlan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(): View
    {
        // 1. Statistik Katalog Pelatihan
        $totalCourses = Training::count();
        $internalCourses = Training::where('type', 'internal')->count();
        $externalCourses = Training::where('type', 'external')->count();

        // 2. Statistik Persetujuan (Pending di tahap LP)
        $pendingApprovals = TrainingPlan::where('status', 'pending_lp')->count();

        // 3. Daftar Tugas Mendesak (Training Plan yang butuh persetujuan LP)
        $pendingTasks = TrainingPlan::where('status', 'pending_lp')
            ->with(['user.position', 'items.training'])
            ->orderBy('submitted_at', 'asc')
            ->take(5)
            ->get();

        // 4. Pelatihan Populer (Top 5)
        $popularTrainings = \Illuminate\Support\Facades\DB::table('training_plan_items')
            ->join('trainings', 'training_plan_items.training_id', '=', 'trainings.id')
            ->select('trainings.title', 'trainings.type', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('trainings.title', 'trainings.type')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('lp.dashboard', [
            'totalCourses' => $totalCourses,
            'internalCourses' => $internalCourses,
            'externalCourses' => $externalCourses,
            'pendingApprovals' => $pendingApprovals,
            'pendingTasks' => $pendingTasks,
            'popularTrainings' => $popularTrainings
        ]);
    }
}