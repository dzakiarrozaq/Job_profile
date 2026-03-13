<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GapRecord;
use App\Models\Position; // Pastikan Model Position diimport
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CompetencyExport;

class LaporanController extends Controller
{
    public function index()
    {
        $supervisor = Auth::user();
        $supervisorPositionId = $supervisor->position_id;

        if (!$supervisorPositionId) {
            return view('supervisor.laporan.index', ['teamGaps' => collect(), 'employees' => collect()]);
        }

        // 1. AMBIL SEMUA ID POSISI DI BAWAH SUPERVISOR (REKURSIF)
        // Menggunakan Raw Query untuk Recursive CTE agar performa cepat
        $subordinatePositionIds = DB::select("
            WITH RECURSIVE hierarchy AS (
                -- Anchor member: ambil posisi yang atasan_id-nya adalah posisi GM
                SELECT id FROM positions WHERE atasan_id = ?
                UNION ALL
                -- Recursive member: ambil posisi yang atasan_id-nya ada di dalam list hierarchy
                SELECT p.id FROM positions p
                INNER JOIN hierarchy h ON p.atasan_id = h.id
            )
            SELECT id FROM hierarchy
        ", [$supervisorPositionId]);

        // Ubah hasil array object menjadi array ID biasa
        $allPositionIds = collect($subordinatePositionIds)->pluck('id')->toArray();

        // 2. AMBIL SEMUA USER ID YANG MENDUDUKI POSISI-POSISI TERSEBUT
        $teamIds = User::whereIn('position_id', $allPositionIds)->pluck('id');

        // 3. QUERY TEAM GAPS (Sama seperti sebelumnya, tapi dengan teamIds yang sudah lengkap)
        $teamGaps = GapRecord::whereIn('user_id', $teamIds)
                        ->whereHas('user.employeeProfile', function($q) {
                            $q->where('status', 'verified');
                        })
                        ->selectRaw('user_id, AVG(gap_value) as avg_gap')
                        ->groupBy('user_id')
                        ->with('user')
                        ->get();

        // 4. QUERY EMPLOYEES (Sama seperti sebelumnya)
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
        // Pastikan di CompetencyExport juga menggunakan logika rekursif yang sama
        return Excel::download(new CompetencyExport, 'laporan_kompetensi_tim.xlsx');
    }
}