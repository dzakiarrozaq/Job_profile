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
use App\Models\User;
use App\Models\TrainingPlanItem;

class PersetujuanController extends Controller
{
    /**
     * Halaman Utama Dashboard Persetujuan
     */
    public function index(): View
    {
        $supervisor = Auth::user();
        
        // --- PERBAIKAN UTAMA DI SINI ---
        // Kita TIDAK menggunakan 'manager_id' dari tabel users.
        // Kita menggunakan fungsi helper getAllSubordinateUserIds yang mengecek tabel POSITIONS.
        
        // 1. Ambil ID User Bawahan (Untuk Training, IDP, Assessment)
        $teamMemberIds = $this->getAllSubordinateUserIds($supervisor);
        
        // 2. Ambil ID Posisi Bawahan (Untuk Job Profile)
        $childPositionIds = $supervisor->position_id ? $this->getAllSubordinatePositionIds($supervisor->position_id) : [];

        // --- QUERY DATA ---

        // A. Assessment
        $assessments = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user.position')
            ->get()
            ->unique('user_id');

        // B. Training Plan
        $trainings = TrainingPlan::where('status', 'pending_supervisor')
            ->whereIn('user_id', $teamMemberIds)
            ->with(['user', 'items.training'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // C. Job Profile
        $jobProfiles = JobProfile::whereIn('position_id', $childPositionIds)
            ->where('status', 'pending_verification')
            ->with('position', 'creator')
            ->orderBy('updated_at', 'desc')
            ->get();

        // D. IDP (Individual Development Plan)
        $pendingIdps = Idp::with('user')
            ->whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->latest()
            ->get();

        // E. Sertifikat
        $pendingCertificates = TrainingPlanItem::whereHas('plan', function($q) use ($teamMemberIds) {
                $q->whereIn('user_id', $teamMemberIds);
            })
            ->where('certificate_status', 'pending_approval')
            ->with(['plan.user']) 
            ->latest()
            ->get();

        return view('supervisor.persetujuan.index', compact(
            'assessments', 'trainings', 'jobProfiles', 'pendingIdps', 'pendingCertificates'
        ));
    }

    // =========================================================================
    // BAGIAN 1: APPROVAL TRAINING PLAN
    // =========================================================================

    public function showTraining($id)
    {
        $plan = TrainingPlan::with(['user', 'items.training'])->findOrFail($id);
        if (!$this->checkIfSubordinate($plan->user)) abort(403, 'Akses ditolak: Bukan bawahan Anda.');
        return view('supervisor.persetujuan.show_training', compact('plan'));
    }

    public function approveTraining($id)
    {
        $plan = TrainingPlan::findOrFail($id);
        if (!$this->checkIfSubordinate($plan->user)) abort(403, 'Anda bukan supervisor karyawan ini.');

        $plan->update(['status' => 'pending_lp', 'supervisor_approved_at' => now()]);
        AuditLog::record('APPROVE PLAN', 'Menyetujui training bawahan', $plan);

        return redirect()->route('supervisor.persetujuan')->with('success', 'Rencana Pelatihan Disetujui.');
    }

    public function rejectTraining(Request $request, $id)
    {
        $plan = TrainingPlan::findOrFail($id);
        if (!$this->checkIfSubordinate($plan->user)) abort(403, 'Anda bukan supervisor karyawan ini.');

        $plan->update(['status' => 'rejected']);
        AuditLog::record('REJECT PLAN', 'Menolak training. Alasan: ' . $request->reason, $plan);

        return redirect()->route('supervisor.persetujuan')->with('error', 'Rencana pelatihan ditolak.');
    }

    // =========================================================================
    // BAGIAN 2: APPROVAL IDP
    // =========================================================================

    public function showIdp($id)
    {
        $idp = Idp::with(['user', 'details'])->findOrFail($id);
        if (!$this->checkIfSubordinate($idp->user)) abort(403, 'Akses Ditolak.');
        return view('supervisor.persetujuan.show_idp', compact('idp'));
    }

    public function approveIdp($id)
    {
        $idp = Idp::findOrFail($id);
        if (!$this->checkIfSubordinate($idp->user)) abort(403, 'Gagal Approve: Bukan bawahan Anda.');

        $idp->update([
            'status' => 'approved',
            'approved_at' => now(),
            'manager_id' => Auth::id()
        ]);

        AuditLog::record('APPROVE IDP', 'Menyetujui IDP bawahan: ' . $idp->user->name, $idp);
        return redirect()->route('supervisor.persetujuan')->with('success', 'IDP Berhasil Disetujui.');
    }

    public function rejectIdp(Request $request, $id)
    {
        $idp = Idp::findOrFail($id);
        if (!$this->checkIfSubordinate($idp->user)) abort(403, 'Gagal Reject: Bukan bawahan Anda.');

        $idp->update(['status' => 'rejected']);
        AuditLog::record('REJECT IDP', 'Menolak IDP. Alasan: ' . $request->reason, $idp);
        return redirect()->route('supervisor.persetujuan')->with('error', 'IDP telah ditolak.');
    }

    // =========================================================================
    // HELPER FUNCTIONS (KUNCI PERBAIKAN EROR)
    // =========================================================================

    /**
     * Mengambil semua ID User bawahan (Level 1 & 2) berdasarkan JABATAN (Position)
     * Menggantikan logika 'where manager_id' yang eror.
     */
    private function getAllSubordinateUserIds($supervisorUser)
    {
        if (!$supervisorUser->position_id) return [];

        // 1. Ambil ID Posisi Bawahan
        $positionIds = $this->getAllSubordinatePositionIds($supervisorUser->position_id);
        
        // 2. Cari User yang memegang posisi tersebut
        return User::whereIn('position_id', $positionIds)->pluck('id')->toArray();
    }

    /**
     * Mengambil ID Posisi Bawahan secara Rekursif (Anak + Cucu)
     */
    private function getAllSubordinatePositionIds($supervisorPositionId)
    {
        // Level 1: Bawahan Langsung
        $directIds = Position::where('atasan_id', $supervisorPositionId)->pluck('id')->toArray();
        
        // Level 2: Bawahan Tidak Langsung (Cucu)
        $indirectIds = [];
        if (!empty($directIds)) {
            $indirectIds = Position::whereIn('atasan_id', $directIds)->pluck('id')->toArray();
        }

        return array_merge($directIds, $indirectIds);
    }

    /**
     * Validasi Keamanan: Cek apakah user target benar-benar bawahan
     */
    private function checkIfSubordinate($targetUser)
    {
        $supervisor = Auth::user();

        if (!$supervisor->position_id || !$targetUser->position_id) {
            return false;
        }

        $allowedPositionIds = $this->getAllSubordinatePositionIds($supervisor->position_id);

        return in_array($targetUser->position_id, $allowedPositionIds);
    }
}