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
use App\Models\User; // Tambahkan ini

class PersetujuanController extends Controller
{
    /**
     * Halaman Utama Dashboard Persetujuan
     */
    public function index(): View
    {
        $supervisor = Auth::user();
        $supervisorPositionId = $supervisor->position_id;
        
        // --- 1. PERBAIKAN: Ambil ID bawahan (Pakai Relasi Position) ---
        $teamMemberIds = User::whereHas('position', function($query) use ($supervisorPositionId) {
            $query->where('atasan_id', $supervisorPositionId);
        })->pluck('id');

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

        // 6. Data Sertifikat
        $pendingCertificates = \App\Models\TrainingPlanItem::whereHas('plan', function($q) use ($teamMemberIds) {
                $q->whereIn('user_id', $teamMemberIds);
            })
            ->where('certificate_status', 'pending_approval')
            ->with(['plan.user']) 
            ->latest()
            ->get();

        return view('supervisor.persetujuan.index', compact(
            'assessments', 
            'trainings', 
            'jobProfiles', 
            'pendingIdps',
            'pendingCertificates'
        ));
    }

    /**
     * Menampilkan Detail Pengajuan
     */
    public function show($id)
    {
        $plan = TrainingPlan::with(['user', 'items.training'])->findOrFail($id);
        
        // --- PERBAIKAN VALIDASI AKSES ---
        // Cek apakah user pemilik rencana adalah bawahan supervisor ini
        $isSubordinate = $this->checkIfSubordinate($plan->user);

        if (!$isSubordinate) {
            abort(403, 'Anda tidak memiliki akses ke data karyawan ini.');
        }

        return view('supervisor.persetujuan.show', compact('plan'));
    }

    /**
     * Aksi Menyetujui Rencana
     */
    public function approve($id)
    {
        $plan = TrainingPlan::findOrFail($id);
        
        // --- PERBAIKAN VALIDASI AKSES ---
        if (!$this->checkIfSubordinate($plan->user)) {
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
     * Aksi Menolak Rencana
     */
    public function reject(Request $request, $id)
    {
        $plan = TrainingPlan::findOrFail($id);
        
        // --- PERBAIKAN VALIDASI AKSES ---
        if (!$this->checkIfSubordinate($plan->user)) {
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

    /**
     * Helper Function: Cek apakah user adalah bawahan supervisor yang login
     */
    private function checkIfSubordinate($targetUser)
    {
        $supervisor = Auth::user();

        // Jika user tidak punya posisi, otomatis bukan bawahan
        if (!$targetUser->position_id) return false;

        // Ambil data posisi bawahan
        $subordinatePosition = Position::find($targetUser->position_id);

        // Cek apakah atasan dari posisi tersebut adalah posisi supervisor kita
        return $subordinatePosition && $subordinatePosition->atasan_id == $supervisor->position_id;
    }
}