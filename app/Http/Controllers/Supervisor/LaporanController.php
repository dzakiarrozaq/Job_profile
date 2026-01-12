<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GapRecord;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CompetencyExport;

class LaporanController extends Controller
{
    public function index()
    {
        $supervisor = Auth::user();
        
        $teamIds = $supervisor->subordinates()->pluck('id');

        $teamGaps = GapRecord::whereIn('user_id', $teamIds)
                        ->whereHas('user.employeeProfile', function($q) {
                            $q->where('status', 'verified');
                        })
                        ->selectRaw('user_id, AVG(gap_value) as avg_gap')
                        ->groupBy('user_id')
                        ->with('user')
                        ->get();

        $employees = User::whereIn('id', $teamIds)
                        ->whereHas('employeeProfile', function($q) {
                            $q->where('status', 'verified');
                        })
                        ->with(['position', 'gapRecords' => function($query) {
                            $query->orderBy('gap_value', 'asc');
                        }])
                        ->paginate(5); 

        return view('supervisor.laporan.index', [
            'teamGaps' => $teamGaps,
            'employees' => $employees 
        ]);
    }

    public function export()
    {
        return Excel::download(new CompetencyExport, 'laporan_kompetensi_tim.xlsx');
    }
}