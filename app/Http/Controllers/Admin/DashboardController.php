<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Models\JobProfile;
use App\Models\Training;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dasbor Admin.
     */
    public function index(): View
    {

        $totalPengguna = User::count();

        $totalJobProfile = JobProfile::count();

        $totalPelatihan = Training::where('status', 'approved')->count();
        
        $persetujuanTertunda = Training::whereIn('status', ['pending_supervisor', 'pending_lp'])->count();

        $recentLogs = AuditLog::with('user') 
                            ->orderBy('created_at', 'desc') 
                            ->take(5)
                            ->get();
        
        $pendingKatalog = Training::where('created_by', Auth::id()) 
                            ->whereIn('status', ['pending_supervisor', 'pending_lp'])
                            ->orderBy('created_at', 'desc')
                            ->take(3)
                            ->get();

        return view('admin.dashboard', [
            'totalPengguna' => $totalPengguna,
            'totalJobProfile' => $totalJobProfile,
            'totalPelatihan' => $totalPelatihan,
            'persetujuanTertunda' => $persetujuanTertunda,
            'recentLogs' => $recentLogs,
            'pendingKatalog' => $pendingKatalog,
        ]);
    }
}