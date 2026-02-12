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
     * Dikelompokkan berdasarkan User (Karyawan)
     */
    public function index()
    {
        // 1. Ambil data dengan Eager Loading yang tepat
        // Hanya ambil Plan yang statusnya 'pending_lp' DAN punya item yang 'approved' (lolos SPV)
        $usersWithPlans = TrainingPlan::where('status', 'pending_lp')
            ->whereHas('items', function($q) {
                $q->where('status', 'approved'); 
            })
            ->with(['user.position', 'approver', 'items' => function($q) {
                 $q->where('status', 'approved'); // Hitung item yang valid saja
            }])
            ->get()
            ->groupBy('user_id'); // 2. Grouping berdasarkan User ID

        // 3. Mapping data agar mudah dipakai di View Index
        $groupedPlans = $usersWithPlans->map(function ($plans) {
            $firstPlan = $plans->first();
            return (object) [
                'user'          => $firstPlan->user,
                'user_id'       => $firstPlan->user_id,
                'total_plans'   => $plans->count(),
                'total_items'   => $plans->sum(fn($p) => $p->items->count()),
                'latest_update' => $plans->max('updated_at'),
                // Ambil nama supervisor dari relasi approver (yang kita buat di Model tadi)
                'approver_name' => $firstPlan->approver->name ?? 'Supervisor', 
            ];
        });

        return view('lp.persetujuan.index', compact('groupedPlans'));
    }

    /**
     * METHOD BARU: Menampilkan semua plan milik satu user
     * Dipanggil saat LP klik "Review Semua" di halaman index
     */
    public function reviewByUser($userId)
    {
        // Ambil semua plan milik user ini yang statusnya pending_lp
        $plans = TrainingPlan::where('user_id', $userId)
            ->where('status', 'pending_lp')
            ->whereHas('items', function($q) {
                 $q->where('status', 'approved'); 
            })
            ->with(['items' => function($q) {
                 $q->where('status', 'approved')->with('training');
            }, 'user.position', 'approver']) // Load approver (Supervisor)
            ->get();

        // Jika data kosong (misal sudah diapprove semua barusan), balik ke index
        if ($plans->isEmpty()) {
            return redirect()->route('lp.persetujuan.index')
                ->with('success', 'Seluruh pengajuan user ini telah selesai diproses.');
        }

        $user = $plans->first()->user;

        // Pastikan Anda membuat view baru: resources/views/lp/persetujuan/review-user.blade.php
        // (Isinya mirip dengan review-user milik Supervisor, tapi tombol aksinya mengarah ke route LP)
        return view('lp.persetujuan.show', compact('plans', 'user'));
    }

    /**
     * Menampilkan Halaman Review untuk User tertentu
     * URL: /lp/persetujuan/{user_id}
     */
    public function show($id) // <--- JANGAN pakai (TrainingPlan $plan), pakai ($id) saja
    {
        $userId = $id; 

        // 1. Ambil plan berdasarkan USER_ID, bukan ID Plan
        $plans = TrainingPlan::where('user_id', $userId)
            ->where('status', 'pending_lp')
            ->whereHas('items', function($q) {
                 $q->where('status', 'approved'); 
            })
            ->with(['items' => function($q) {
                 $q->where('status', 'approved')->with('training');
            }, 'user.position', 'approver']) 
            ->get();

        // 2. Jika kosong, kembalikan ke index (bukan error 404)
        if ($plans->isEmpty()) {
            return redirect()->route('lp.persetujuan.index')
                ->with('success', 'Data tidak ditemukan atau sudah diproses.');
        }

        $user = $plans->first()->user;

        return view('lp.persetujuan.show', compact('plans', 'user'));
    }

    /**
     * Aksi Verifikasi (Final Approve)
     */
    public function approve($id)
    {
        $plan = TrainingPlan::with('user')->findOrFail($id);
        
        $plan->update([
            'status' => 'approved', 
            'lp_approved_at' => now(), // Tambahkan timestamp
            // 'approved_by_lp' => Auth::id() // Opsional: jika ada kolom ini
        ]);

        // Kirim Notifikasi
        // try {
        //     $plan->user->notify(new StatusDiperbarui(
        //         'Disetujui Final (LP)', 
        //         'Selamat! Rencana pelatihan Anda telah disetujui Learning Partner.',
        //         route('riwayat'), 
        //         'success' 
        //     ));
        // } catch (\Exception $e) { /* Abaikan error mail */ }

        AuditLog::record('APPROVE PLAN (LP)', 'Memverifikasi final rencana pelatihan milik: ' . $plan->user->name, $plan);

        return back()->with('success', 'Rencana training berhasil disetujui.');
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
            'rejection_reason' => $alasan            
        ]);

        // Kirim Notifikasi
        // try {
        //     $plan->user->notify(new StatusDiperbarui(
        //         'Ditolak Learning Partner', 
        //         'Maaf, rencana pelatihan Anda ditolak oleh LP. Alasan: ' . $alasan,
        //         route('riwayat'), 
        //         'error' 
        //     ));
        // } catch (\Exception $e) { /* Abaikan error mail */ }

        AuditLog::record('REJECT PLAN (LP)', 'Menolak rencana pelatihan milik: ' . $plan->user->name . '. Alasan: ' . $alasan, $plan);

        return back()->with('error', 'Rencana training ditolak.');
    }
}