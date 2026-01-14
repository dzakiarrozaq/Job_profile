<?php

namespace App\Http\Controllers\Lp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingPlan;
use Illuminate\Support\Facades\Auth;
use App\Notifications\StatusDiperbarui; 
use App\Models\AuditLog; 

class PersetujuanController extends Controller
{
    /**
     * Menampilkan daftar rencana yang SUDAH disetujui Supervisor
     * dan menunggu verifikasi Learning Partner.
     */
    public function index()
    {
        $plans = TrainingPlan::with(['user.position', 'items.training'])
            ->where('status', 'pending_lp') 
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('lp.persetujuan.index', compact('plans'));
    }

    /**
     * Menampilkan Detail
     */
    public function show($id)
    {
        $plan = TrainingPlan::with(['user.position', 'items.training', 'user.manager'])
            ->findOrFail($id);

        return view('lp.persetujuan.show', compact('plan'));
    }

    /**
     * Aksi Verifikasi (Final Approve)
     */
    public function approve($id)
    {
        $plan = TrainingPlan::with('user')->findOrFail($id);
        
        $plan->update([
            'status' => 'approved', 
        ]);

        $plan->user->notify(new StatusDiperbarui(
            'Disetujui Final (LP)', 
            'Selamat! Rencana pelatihan Anda telah disetujui Learning Partner. Anda dapat memulai pelatihan/mengupload sertifikat.', // Pesan
            route('riwayat'), 
            'success' 
        ));

        AuditLog::record('APPROVE PLAN (LP)', 'Memverifikasi final rencana pelatihan milik: ' . $plan->user->name, $plan);

        return redirect()->route('lp.persetujuan')
            ->with('success', 'Rencana training berhasil diverifikasi Final.');
    }

    /**
     * Aksi Tolak (Reject)
     */
    public function reject(Request $request, $id)
    {
        $plan = TrainingPlan::with('user')->findOrFail($id);

        $alasan = $request->input('reason', 'Tidak ada alasan spesifik.');

        $plan->update([
            'status' => 'rejected',            
        ]);

        $plan->user->notify(new StatusDiperbarui(
            'Ditolak Learning Partner', 
            'Maaf, rencana pelatihan Anda ditolak oleh Learning Partner. Alasan: ' . $alasan, // Pesan
            route('riwayat'), 
            'error' 
        ));

        AuditLog::record('REJECT PLAN (LP)', 'Menolak rencana pelatihan milik: ' . $plan->user->name . '. Alasan: ' . $alasan, $plan);

        return redirect()->route('lp.persetujuan')
            ->with('error', 'Rencana training ditolak.');
    }
}