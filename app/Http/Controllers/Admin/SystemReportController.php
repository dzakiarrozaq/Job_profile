<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\GapRecord;
use App\Models\Training;
use App\Models\Organization; // Pengganti Department
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Models\JobProfile;
use App\Exports\SystemReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;

class SystemReportController extends Controller
{
    public function index(Request $request)
    {
        // =====================================================================
        // 1. STATISTIK UMUM (KARTU ATAS)
        // =====================================================================
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $usersByRole = DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->get();

        $totalPositions = Position::count();
        $coveredPositions = JobProfile::where('status', 'verified')->count();
        $draftProfiles = JobProfile::where('status', '!=', 'verified')->count();
        $coverageRatio = $totalPositions > 0 ? ($coveredPositions / $totalPositions) * 100 : 0;


        // =====================================================================
        // 2. FILTER & QUERY DATA (UNTUK GRAFIK & TABEL)
        // =====================================================================
        
        $queryGap = GapRecord::with(['user.position.organization']);
        
        if ($request->department_id && $request->department_id != 'all') {
            $queryGap->whereHas('user.position', fn($q) => 
                $q->where('organization_id', $request->department_id)
            );
        }

        if ($request->position_id && $request->position_id != 'all') {
            $queryGap->whereHas('user', fn($q) => 
                $q->where('position_id', $request->position_id)
            );
        }

        $criticalCompetencies = collect();
        if (Schema::hasTable('gap_records')) {
            $criticalCompetencies = (clone $queryGap)
                ->select('competency_name', DB::raw('AVG(gap_value) as avg_gap'))
                ->groupBy('competency_name')
                ->orderBy('avg_gap', 'asc') 
                ->take(5)
                ->get();
        }

        $popularTrainings = collect();
        if (Schema::hasTable('training_plan_items')) {
            $popularTrainings = DB::table('training_plan_items')
                ->join('trainings', 'training_plan_items.training_id', '=', 'trainings.id')
                ->select('trainings.title', DB::raw('count(*) as total_participants'))
                ->groupBy('trainings.title')
                ->orderByDesc('total_participants')
                ->take(5)
                ->get();
        }

        // =====================================================================
        // 3. DATA PER KARYAWAN (UNTUK TABEL DETAIL)
        // =====================================================================
        $employeesQuery = User::whereHas('gapRecords')
            ->with(['position.organization', 'gapRecords' => function($q) {
                $q->orderBy('gap_value', 'asc');
            }]);

        if ($request->department_id && $request->department_id != 'all') {
            $employeesQuery->whereHas('position', fn($q) => 
                $q->where('organization_id', $request->department_id)
            );
        }
        if ($request->position_id && $request->position_id != 'all') {
            $employeesQuery->where('position_id', $request->position_id);
        }

        $employees = $employeesQuery->paginate(10);


        // =====================================================================
        // 4. DATA MASTER UNTUK DROPDOWN FILTER
        // =====================================================================
        
        $departments = Organization::where('type', 'department')
                        ->orderBy('name')
                        ->get();

        $positions = Position::orderBy('title')->get();
        $roles = Role::all();

        return view('admin.laporan.index', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'usersByRole' => $usersByRole,
            'totalPositions' => $totalPositions,
            'coveredPositions' => $coveredPositions,
            'draftProfiles' => $draftProfiles,
            'coverageRatio' => $coverageRatio,
            
            'criticalCompetencies' => $criticalCompetencies,
            'popularTrainings' => $popularTrainings,
            
            'employees' => $employees, 
            
            'departments' => $departments,
            'positions' => $positions,
            'roles' => $roles,
            'filters' => $request->all()
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(new SystemReportExport($request), 'laporan_sistem_gap.xlsx');
    }
}