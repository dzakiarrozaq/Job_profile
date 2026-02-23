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
use Illuminate\Support\Facades\Notification;
use App\Notifications\TrainingRejectedNotification;

class PersetujuanController extends Controller
{
    /**
     * Halaman Utama Dashboard Persetujuan
     */
    public function index(): View
    {
        $supervisor = Auth::user();
        
        $teamMemberIds = $this->getAllSubordinateUserIds($supervisor);
        
        $childPositionIds = $supervisor->position_id ? $this->getAllSubordinatePositionIds($supervisor->position_id) : [];

        $assessments = EmployeeProfile::whereIn('user_id', $teamMemberIds)
            ->where('status', 'pending_verification')
            ->with('user.position')
            ->get()
            ->unique('user_id');

        $trainings = TrainingPlan::where('status', 'pending_supervisor')
            ->whereIn('user_id', $teamMemberIds)
            ->whereHas('items', function($q) {
                $q->where('status', 'pending'); 
            })
            ->with(['user', 'items' => function($q) {
                $q->where('status', 'pending')->with('training'); 
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $jobProfiles = JobProfile::whereIn('position_id', $childPositionIds)
            ->where('status', 'pending_verification')
            ->with('position', 'creator')
            ->orderBy('updated_at', 'desc')
            ->get();

        $pendingIdps = Idp::with('user')
            ->whereIn('user_id', $teamMemberIds)
            ->where('status', 'submitted')
            ->latest()
            ->get();

        $pendingCertificates = TrainingPlanItem::whereHas('plan', function($q) use ($teamMemberIds) {
                $q->whereIn('user_id', $teamMemberIds);
            })
            ->where('certificate_status', 'pending_approval')
            ->with(['plan.user']) 
            ->latest()
            ->get()
            ->groupBy(function($item) {
                return $item->plan->user->id; 
            });

        return view('supervisor.persetujuan.index', compact(
            'assessments', 'trainings', 'jobProfiles', 'pendingIdps', 'pendingCertificates'
        ));
    }

    public function showTraining($id)
    {
        $plan = TrainingPlan::with(['user', 'items.training'])->findOrFail($id);
        if (!$this->checkIfSubordinate($plan->user)) abort(403, 'Akses ditolak: Bukan bawahan Anda.');
        return view('supervisor.persetujuan.show', compact('plan'));
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


    public function showIdp($id)
    {
        $idp = Idp::with(['user', 'details'])->findOrFail($id);
        if (!$this->checkIfSubordinate($idp->user)) abort(403, 'Akses Ditolak.');
        return view('supervisor.idp.show', compact('idp'));
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


    /**
     * Mengambil semua ID User bawahan (Level 1 & 2) berdasarkan JABATAN (Position)
     * Menggantikan logika 'where manager_id' yang eror.
     */
    private function getAllSubordinateUserIds($supervisorUser)
    {
        if (!$supervisorUser->position_id) return [];

        $positionIds = $this->getAllSubordinatePositionIds($supervisorUser->position_id);
        
        return User::whereIn('position_id', $positionIds)->pluck('id')->toArray();
    }

    /**
     * Mengambil ID Posisi Bawahan secara Rekursif (Anak + Cucu)
     */
    private function getAllSubordinatePositionIds($supervisorPositionId)
    {
        $directIds = Position::where('atasan_id', $supervisorPositionId)->pluck('id')->toArray();
        
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

    /**
     * Menampilkan Detail Rencana Pelatihan (Training Plan)
     * Method ini dipanggil oleh route: supervisor.rencana.show
     */
    public function show($id)
    {
        $plan = TrainingPlan::with(['user.position', 'items.training'])->findOrFail($id);

        if (!$this->checkIfSubordinate($plan->user)) {
            abort(403, 'Akses ditolak: Karyawan ini bukan bawahan Anda.');
        }

        return view('supervisor.persetujuan.show', compact('plan'));
    }

    /**
     * Menampilkan gabungan semua draft plan milik satu user
     */
    public function reviewByUser($userId)
    {
        
        $plans = TrainingPlan::where('user_id', $userId)
            ->where('status', 'pending_supervisor')
            ->whereHas('items', function($q) {
                $q->where('status', 'pending'); 
            })
            ->with(['items' => function($q) {
                $q->where('status', 'pending')->with('training'); 
            }, 'user.position'])
            ->get();

        if($plans->isEmpty()) {
            return redirect()->route('supervisor.persetujuan')
                ->with('success', 'Seluruh pengajuan user ini telah selesai direview.');
        }

        $user = $plans->first()->user;

        return view('supervisor.persetujuan.review-user', compact('plans', 'user'));
    }

    /**
     * Menyetujui SEMUA plan pending milik user tersebut sekaligus
     */
    public function approveByUser($userId)
    {
        $plans = TrainingPlan::where('user_id', $userId)->where('status', 'pending_supervisor')->get();
        
        foreach($plans as $plan) {
            $plan->update(['status' => 'pending_lp', 'supervisor_approved_at' => now()]);
        }

        return redirect()->route('supervisor.persetujuan')->with('success', 'Semua pengajuan karyawan tersebut telah disetujui.');
    }

    /**
     * Menolak SEMUA plan pending milik user tersebut
     */
    public function rejectByUser(Request $request, $userId)
    {
        $plans = TrainingPlan::where('user_id', $userId)->where('status', 'pending_supervisor')->get();
        
        foreach($plans as $plan) {
            $plan->update(['status' => 'rejected', 'rejection_reason' => $request->reason]);
        }

        return redirect()->route('supervisor.persetujuan')->with('success', 'Pengajuan ditolak.');
    }

    public function approveItem($itemId)
    {
        $item = TrainingPlanItem::findOrFail($itemId);
        $userId = $item->plan->user_id; 

        $item->update(['status' => 'approved']);

        $parentPlan = $item->plan;
        $sisaPendingDiPlanIni = $parentPlan->items()->where('status', 'pending')->count();

        if ($sisaPendingDiPlanIni == 0) {
            $parentPlan->update([
                'status' => 'pending_lp', 
                'supervisor_approved_at' => now(),
                'approved_by' => Auth::id()
            ]);
        }

        $masihAdaItemPendingLain = TrainingPlanItem::whereHas('plan', function($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'pending_supervisor');
            })
            ->where('status', 'pending')
            ->exists();

        if (!$masihAdaItemPendingLain) {
            return redirect()->route('supervisor.persetujuan')
                ->with('success', 'Seluruh pengajuan user ini telah selesai diproses.');
        }

        return back()->with('success', 'Item disetujui. Silakan lanjut ke item berikutnya.');
    }

    public function rejectItem(Request $request, $itemId)
    {
        $request->validate(['reason' => 'required|string|max:255']);
        
        $item = TrainingPlanItem::findOrFail($itemId);
        $userId = $item->plan->user_id;

        $item->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);

        $parentPlan = $item->plan;
        $sisaPendingDiPlanIni = $parentPlan->items()->where('status', 'pending')->count();

        if ($sisaPendingDiPlanIni == 0) {
            $totalApproved = $parentPlan->items()->where('status', 'approved')->count();
            
            $newStatus = ($totalApproved > 0) ? 'pending_lp' : 'rejected';
            
            $parentPlan->update([
                'status' => $newStatus,
                'supervisor_approved_at' => now(),
                'approved_by' => Auth::id()
            ]);
        }

        $masihAdaItemPendingLain = TrainingPlanItem::whereHas('plan', function($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'pending_supervisor');
            })
            ->where('status', 'pending')
            ->exists();

        if (!$masihAdaItemPendingLain) {
            return redirect()->route('supervisor.persetujuan')
                ->with('success', 'Review untuk user ini selesai.');
        }

        return back()->with('success', 'Item ditolak. Silakan lanjut review sisa item.');
    }

    public function rejectSertifikat(Request $request, $id)
    {
        $item = TrainingPlanItem::findOrFail($id);
        
        $item->update([
            'certificate_status' => 'rejected', 
            'rejection_reason'   => $request->reason 
        ]);

        return back()->with('error', 'Sertifikat berhasil ditolak.');
    }
}

