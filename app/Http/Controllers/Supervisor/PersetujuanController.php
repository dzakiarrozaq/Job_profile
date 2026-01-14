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
use App\Models\AuditLog;

class PersetujuanController extends Controller
{
    /**
     * Halaman Utama Dashboard Persetujuan
     */
    public function index(): View
    {
        $supervisor = Auth::user();
        
        // 1. Ambil ID bawahan
        $teamMemberIds = \App\Models\User::where('manager_id', $supervisor->id)->pluck('id');

        // 2. Data Assessment
        $assessments = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user.position')
            ->select('user_id', 'submitted_at')
            ->get()
            ->unique('user_id');

        // 3. Data Training Plan (Rencana Pelatihan)
        $trainings = TrainingPlan::where('status', 'pending_supervisor')
            ->whereIn('user_id', $teamMemberIds) 
            ->with(['user', 'items.training']) 
            ->orderBy('created_at', 'desc')
            ->get();
        
        // 4. Data Job Profile
        $supervisorPositionId = $supervisor->position_id;
        $childPositionIds = Position::where('atasan_id', $supervisorPositionId)->pluck('id');

        $jobProfiles = JobProfile::whereIn('position_id', $childPositionIds)
            ->where('status', 'pending_verification')
            ->with('position', 'creator')
            ->orderBy('updated_at', 'desc')
            ->get();

        // 5. Data IDP
        $pendingIdps = Idp::with('user')
            ->whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->latest()
            ->get();

        // 6. Data Sertifikat (TAMBAHAN BARU DISINI)
        // Mengambil item pelatihan yang status sertifikatnya 'pending_approval' milik bawahan
        $pendingCertificates = \App\Models\TrainingPlanItem::whereHas('plan', function($q) use ($teamMemberIds) {
                $q->whereIn('user_id', $teamMemberIds);
            })
            ->where('certificate_status', 'pending_approval')
            ->with(['plan.user']) // Load data user pemilik rencana
            ->latest()
            ->get();

        return view('supervisor.persetujuan.index', compact(
            'assessments', 
            'trainings', 
            'jobProfiles', 
            'pendingIdps',
            'pendingCertificates' // <--- Jangan lupa masukkan ke compact
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
        
        if ($plan->user->manager_id !== Auth::id()) {
            abort(403);
        }

        $plan->update([
            'status' => 'pending_lp', 
            'supervisor_approved_at' => now(),
        ]);

        AuditLog::record('APPROVE PLAN', 'Menyetujui rencana pelatihan bawahan', $plan);

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

        AuditLog::record('REJECT PLAN', 'Menolak rencana pelatihan. Alasan: ' . $request->reason, $plan);

        return redirect()->route('supervisor.persetujuan')
            ->with('error', 'Rencana pelatihan telah ditolak.');
    }
}