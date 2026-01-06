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
use App\Models\Idp; 

class PersetujuanController extends Controller
{
    /**
     * Halaman Utama Dashboard Persetujuan
     */
    public function index(): View
    {
        $supervisor = Auth::user();
        
        $teamMemberIds = \App\Models\User::where('manager_id', $supervisor->id)->pluck('id');

        $assessments = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user.position')
            ->select('user_id', 'submitted_at')
            ->get()
            ->unique('user_id');

        
        $trainings = TrainingPlan::where('status', 'pending_supervisor')
            ->whereIn('user_id', $teamMemberIds) 
            ->with(['user', 'items.training']) 
            ->orderBy('created_at', 'desc')
            ->get();
        
        $supervisorPositionId = $supervisor->position_id;
        $childPositionIds = Position::where('atasan_id', $supervisorPositionId)->pluck('id');

        $jobProfiles = JobProfile::whereIn('position_id', $childPositionIds)
            ->where('status', 'pending_verification')
            ->with('position', 'creator')
            ->orderBy('updated_at', 'desc')
            ->get();

        $pendingIdps = Idp::with('user')
            ->whereIn('user_id', $teamMemberIds) // Konsisten pakai variable yang sama
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

    /**
     * Menampilkan Detail Pengajuan (Method Baru)
     */
    public function show($id)
    {
        $plan = TrainingPlan::with(['user', 'items.training'])->findOrFail($id);

        if ($plan->user->manager_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke data karyawan ini.');
        }

        return view('supervisor.persetujuan.show', compact('plan'));
    }

    /**
     * Aksi Menyetujui Rencana (Method Baru)
     */
    public function approve($id)
    {
        $plan = TrainingPlan::findOrFail($id);
        
        // Cek akses keamanan
        if ($plan->user->manager_id !== Auth::id()) {
            abort(403);
        }

        // UPDATE LOGIKA DI SINI:
        // Jangan langsung 'approved', tapi lempar ke 'pending_lp'
        $plan->update([
            'status' => 'pending_lp', // <--- UBAH INI
            'supervisor_approved_at' => now(),
        ]);

        return redirect()->route('supervisor.persetujuan')
            ->with('success', 'Disetujui. Sekarang menunggu verifikasi Learning Partner.');
    }

    /**
     * Aksi Menolak Rencana (Method Baru)
     */
    public function reject(Request $request, $id)
    {
        $plan = TrainingPlan::findOrFail($id);
        
        if ($plan->user->manager_id !== Auth::id()) {
            abort(403);
        }

        // Update Status
        $plan->update([
            'status' => 'rejected',
        ]);

        return redirect()->route('supervisor.persetujuan')
            ->with('error', 'Rencana pelatihan telah ditolak.');
    }
}