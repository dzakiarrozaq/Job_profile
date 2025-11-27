<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\JobProfile;
use App\Models\Position;
use App\Models\EmployeeProfile;
use Illuminate\Support\Facades\DB;

class SystemReportController extends Controller
{
    public function index()
    {
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

        $departments = Department::withCount(['users' => function($q) {
            $q->where('status', 'active');
        }])->get()->map(function($dept) {
            
            $verifiedUsersCount = User::where('department_id', $dept->id)
                ->whereHas('employeeProfiles', function($q) {
                    $q->where('status', 'verified');
                })->count();

            $dept->completion_rate = $dept->users_count > 0 
                ? round(($verifiedUsersCount / $dept->users_count) * 100, 1) 
                : 0;

            return $dept;
        })->sortByDesc('completion_rate'); // Urutkan dari yang paling rajin

        return view('admin.laporan.index', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'usersByRole' => $usersByRole,
            'totalPositions' => $totalPositions,
            'coveredPositions' => $coveredPositions,
            'draftProfiles' => $draftProfiles,
            'coverageRatio' => $coverageRatio,
            'departments' => $departments
        ]);
    }
}