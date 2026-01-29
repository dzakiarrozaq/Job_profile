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
        
        // 1. PERBAIKAN: Ambil ID Bawahan berdasarkan Posisi (Bukan kolom manager_id)
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
        
        // 2. PERBAIKAN: Validasi menggunakan Helper Position
        if (!$this->checkIfSubordinate($idp->user)) {
            abort(403, 'Akses Ditolak: Anda bukan atasan karyawan ini (berdasarkan struktur jabatan).');
        }

        return view('supervisor.idp.show', compact('idp'));
    }

    public function update(Request $request, $id)
    {
        $idp = Idp::findOrFail($id);

        // 3. PERBAIKAN: Validasi menggunakan Helper Position
        if (!$this->checkIfSubordinate($idp->user)) {
            abort(403, 'Akses Ditolak.');
        }

        if ($request->action == 'approve') {
            $idp->update([
                'status' => 'approved',
                'manager_id' => Auth::id(), // Catat siapa yang approve
                'approved_at' => now(),
            ]);

            // Kirim Notifikasi (Pastikan user punya email valid/setup notifikasi benar)
            try {
                $idp->user->notify(new StatusDiperbarui(
                    'IDP Disetujui', 
                    'Selamat! IDP Anda telah disetujui.', 
                    route('idp.index'), 
                    'success' 
                ));
            } catch (\Exception $e) {
                // Abaikan error notifikasi agar tidak membatalkan approval
            }

            AuditLog::record('APPROVE IDP', 'Menyetujui IDP milik: ' . $idp->user->name, $idp);
            $msg = 'IDP berhasil disetujui.';

        } else {
            $idp->update([
                'status' => 'rejected',
                'manager_id' => Auth::id(), 
                // Pastikan kolom ini ada di migration idps, jika tidak gunakan 'rejection_note' di tabel lain atau log
                // Jika belum ada kolom rejection_note di tabel idps, hapus baris ini:
                // 'rejection_note' => $request->rejection_note, 
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

        // Redirect kembali ke halaman persetujuan dashboard
        return redirect()->route('supervisor.persetujuan') 
                         ->with('success', $msg);
    }

    // =========================================================================
    // HELPER FUNCTIONS (Logic Hirarki Jabatan)
    // =========================================================================

    /**
     * Helper: Cek apakah targetUser adalah bawahan dari Supervisor yg login
     */
    private function checkIfSubordinate($targetUser)
    {
        $supervisor = Auth::user();

        // Cek kelengkapan data posisi
        if (!$supervisor->position_id || !$targetUser->position_id) {
            return false; 
        }

        // Ambil semua ID posisi di bawah supervisor (Level 1 & 2)
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
        // Level 1: Bawahan Langsung
        $directIds = Position::where('atasan_id', $supervisorPositionId)->pluck('id')->toArray();
        
        // Level 2: Cucu Buah
        $indirectIds = [];
        if(!empty($directIds)){
            $indirectIds = Position::whereIn('atasan_id', $directIds)->pluck('id')->toArray();
        }

        return array_merge($directIds, $indirectIds);
    }
}