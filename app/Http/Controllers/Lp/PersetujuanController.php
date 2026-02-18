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
        // 1. AMBIL DATA LEBIH LOOSE (LONGGAR)
        // Cukup ambil yang status Plan-nya 'pending_lp'.
        // Kita load relasi items agar bisa dihitung nanti, tapi TIDAK memfilter query utama berdasarkan item.
        $plans = TrainingPlan::with(['user.position', 'items', 'approver']) 
            ->where('status', 'pending_lp')
            ->orderBy('updated_at', 'desc')
            ->get();

        // 2. GROUPING MANUAL
        // Kita kelompokkan data berdasarkan User ID
        $groupedPlans = $plans->groupBy('user_id')->map(function ($userPlans) {
            $firstPlan = $userPlans->first();
            
            return (object) [
                'user'          => $firstPlan->user,
                'user_id'       => $firstPlan->user_id,
                'total_plans'   => $userPlans->count(),
                // Hitung total item dari collection yang sudah ditarik
                'total_items'   => $userPlans->sum(function($p) {
                                        return $p->items->count();
                                   }),
                'latest_update' => $userPlans->max('updated_at'),
                // Pastikan relasi approver aman (cegah error jika null)
                'approver_name' => $firstPlan->approver ? $firstPlan->approver->name : 'Data Kosong (Cek DB)',
            ];
        });

        // 3. Kirim ke View (Gunakan values() untuk reset key array agar urut 0,1,2...)
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
        // Ambil semua plan milik user ini yang statusnya pending_lp
        // Di sini kita load items agar tampil di view detail
        $plans = TrainingPlan::where('user_id', $userId)
            ->where('status', 'pending_lp')
            ->with(['items.training', 'user.position', 'approver']) // Eager load lengkap
            ->get();

        // Jika data kosong (misal sudah diapprove semua barusan atau ID salah), balik ke index
        if ($plans->isEmpty()) {
            return redirect()->route('lp.persetujuan.index')
                ->with('success', 'Seluruh pengajuan user ini telah selesai diproses atau tidak ditemukan.');
        }

        $user = $plans->first()->user;

        // Tampilkan view detail (pastikan file view-nya ada)
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
        
        // Update status Plan
        $plan->update([
            'status' => 'approved', 
            'lp_approved_at' => now(), 
            // 'approved_by_lp' => Auth::id() // Uncomment jika ada kolom ini
        ]);

        // Opsional: Update status item-item di dalamnya menjadi 'approved' juga (jika belum)
        // $plan->items()->update(['status' => 'approved']);

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

        // Update status Plan
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