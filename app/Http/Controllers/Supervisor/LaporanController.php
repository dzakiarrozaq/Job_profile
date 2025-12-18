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
        
        // Ambil semua ID bawahan
        $teamIds = $supervisor->subordinates()->pluck('id');

        // 1. Ambil Rata-rata Gap Tim (HANYA YANG SUDAH VERIFIED)
        $teamGaps = GapRecord::whereIn('user_id', $teamIds)
                        // [FIX] Tambahkan Filter Ini:
                        ->whereHas('user.employeeProfile', function($q) {
                            $q->where('status', 'verified');
                        })
                        ->selectRaw('user_id, AVG(gap_value) as avg_gap')
                        ->groupBy('user_id')
                        ->with('user')
                        ->get();

        // 2. Ambil Daftar Karyawan (HANYA YANG SUDAH VERIFIED)
        $employees = User::whereIn('id', $teamIds)
                        // [FIX] Filter karyawan yang status profilnya 'verified'
                        ->whereHas('employeeProfile', function($q) {
                            $q->where('status', 'verified');
                        })
                        ->with(['position', 'gapRecords' => function($query) {
                            // Urutkan gap dari yang paling minus (prioritas training)
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
        // Catatan: Pastikan di dalam file CompetencyExport juga ditambahkan
        // logika filter ->where('status', 'verified') agar Excel-nya akurat.
        return Excel::download(new CompetencyExport, 'laporan_kompetensi_tim.xlsx');
    }
}