<?php

namespace App\Http\Controllers\Lp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingPlan;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class PersetujuanController extends Controller
{
    /**
     * Menampilkan daftar rencana yang SUDAH disetujui Supervisor
     * Dikelompokkan berdasarkan User (Karyawan)
     */
    public function index()
    {
        $plans = TrainingPlan::with(['user.position', 'items', 'approver']) 
            ->where('status', 'pending_lp')
            ->orderBy('updated_at', 'desc')
            ->get();

        $groupedPlans = $plans->groupBy('user_id')->map(function ($userPlans) {
            $firstPlan = $userPlans->first();
            
            return (object) [
                'user'          => $firstPlan->user,
                'user_id'       => $firstPlan->user_id,
                'total_plans'   => $userPlans->count(),
                'total_items'   => $userPlans->sum(function($p) {
                                        return $p->items->count();
                                   }),
                'latest_update' => $userPlans->max('updated_at'),
                'approver_name' => $firstPlan->approver ? $firstPlan->approver->name : 'Data Kosong (Cek DB)',
            ];
        });

        return view('lp.persetujuan.index', [
            'groupedPlans' => $groupedPlans->values() 
        ]);
    }

    /**
     * Menampilkan semua plan milik satu user
     * Dipanggil saat LP klik "Review Semua" di halaman index
     * Route: /lp/persetujuan/user/{userId}
     */
    public function reviewByUser($userId)
    {
        $plans = TrainingPlan::where('user_id', $userId)
            ->where('status', 'pending_lp')
            ->with(['items.training', 'user.position', 'approver']) 
            ->get();

        if ($plans->isEmpty()) {
            return redirect()->route('lp.persetujuan.index')
                ->with('success', 'Seluruh pengajuan user ini telah selesai diproses atau tidak ditemukan.');
        }

        $user = $plans->first()->user;

        return view('lp.persetujuan.show', compact('plans', 'user'));
    }

    /**
     * Method show() dihapus karena fungsinya sudah digantikan oleh reviewByUser()
     * untuk menghindari kebingungan logic. 
     * Pastikan route di web.php mengarah ke reviewByUser untuk detail.
     */

    /**
     * Aksi Verifikasi (Final Approve) per Plan
     */
    public function approve($id)
    {
        $plan = TrainingPlan::with('user')->findOrFail($id);
        
        $plan->update([
            'status' => 'approved', 
            'lp_approved_at' => now(), 
        ]);

        AuditLog::record('APPROVE PLAN (LP)', 'Memverifikasi final rencana pelatihan milik: ' . $plan->user->name, $plan);

        return back()->with('success', 'Rencana training berhasil disetujui.');
    }

    /**
     * Aksi Tolak (Reject) per Plan
     */
    public function reject(Request $request, $id)
    {
        $plan = TrainingPlan::with('user')->findOrFail($id);
        $alasan = $request->input('reason', 'Tidak ada alasan spesifik.');

        $plan->update([
            'status' => 'rejected',
            'rejection_reason' => $alasan            
        ]);

        // Opsional: Update status item-item menjadi 'rejected'
        // $plan->items()->update(['status' => 'rejected']);

        AuditLog::record('REJECT PLAN (LP)', 'Menolak rencana pelatihan milik: ' . $plan->user->name . '. Alasan: ' . $alasan, $plan);

        return back()->with('error', 'Rencana training ditolak.');
    }
}