<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Idp;
use App\Models\User;
use App\Models\Position; // Tambahkan Model Position
use App\Models\AuditLog; 
use Illuminate\Support\Facades\Auth;
use App\Notifications\StatusDiperbarui; 

class SupervisorIdpController extends Controller
{
    public function index()
    {
        $supervisor = Auth::user();
        
        $subordinateUserIds = $this->getAllSubordinateUserIds($supervisor);

        $pendingIdps = Idp::with('user')
                          ->whereIn('user_id', $subordinateUserIds)
                          ->where('status', 'submitted')
                          ->latest()
                          ->get();

        return view('supervisor.idp.index', compact('pendingIdps'));
    }

    public function show($id)
    {
        $idp = Idp::with(['user', 'details'])->findOrFail($id);
        
        if (!$this->checkIfSubordinate($idp->user)) {
            abort(403, 'Akses Ditolak: Anda bukan atasan karyawan ini (berdasarkan struktur jabatan).');
        }

        return view('supervisor.idp.show', compact('idp'));
    }

    public function update(Request $request, $id)
    {
        $idp = Idp::findOrFail($id);

        if (!$this->checkIfSubordinate($idp->user)) {
            abort(403, 'Akses Ditolak.');
        }

        if ($request->action == 'approve') {
            $idp->update([
                'status' => 'approved',
                'manager_id' => Auth::id(), 
                'approved_at' => now(),
            ]);

            try {
                $idp->user->notify(new StatusDiperbarui(
                    'IDP Disetujui', 
                    'Selamat! IDP Anda telah disetujui.', 
                    route('idp.index'), 
                    'success' 
                ));
            } catch (\Exception $e) {
            }

            AuditLog::record('APPROVE IDP', 'Menyetujui IDP milik: ' . $idp->user->name, $idp);
            $msg = 'IDP berhasil disetujui.';

        } else {
            $idp->update([
                'status' => 'rejected',
                'manager_id' => Auth::id(),
            ]);

            // Kirim Notifikasi
            try {
                $idp->user->notify(new StatusDiperbarui(
                    'IDP Ditolak', 
                    'IDP Anda dikembalikan. Catatan: ' . ($request->rejection_note ?? '-'), 
                    route('idp.index'), 
                    'error' 
                ));
            } catch (\Exception $e) {}

            AuditLog::record('REJECT IDP', 'Menolak IDP milik: ' . $idp->user->name . '. Alasan: ' . $request->rejection_note, $idp);
            $msg = 'IDP ditolak.';
        }

        return redirect()->route('supervisor.persetujuan') 
                         ->with('success', $msg);
    }

    /**
     * Helper: Cek apakah targetUser adalah bawahan dari Supervisor yg login
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
     * Helper: Ambil semua ID User bawahan
     */
    private function getAllSubordinateUserIds($supervisorUser)
    {
        if (!$supervisorUser->position_id) return [];
        $positionIds = $this->getAllSubordinatePositionIds($supervisorUser->position_id);
        return User::whereIn('position_id', $positionIds)->pluck('id')->toArray();
    }

    /**
     * Helper: Ambil semua ID Posisi (Recursive)
     */
    private function getAllSubordinatePositionIds($supervisorPositionId)
    {
        $directIds = Position::where('atasan_id', $supervisorPositionId)->pluck('id')->toArray();
        
        $indirectIds = [];
        if(!empty($directIds)){
            $indirectIds = Position::whereIn('atasan_id', $directIds)->pluck('id')->toArray();
        }

        return array_merge($directIds, $indirectIds);
    }
}