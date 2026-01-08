<?php

namespace App\Http\Controllers\Lp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingPlan;
use Illuminate\Support\Facades\Auth;

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
        $plan = TrainingPlan::findOrFail($id);
        
        $plan->update([
            'status' => 'approved', 
        ]);

        return redirect()->route('lp.persetujuan')
            ->with('success', 'Rencana training berhasil diverifikasi Final.');
    }

    /**
     * Aksi Tolak (Reject)
     */
    public function reject(Request $request, $id)
    {
        $plan = TrainingPlan::findOrFail($id);

        $plan->update([
            'status' => 'rejected',
        ]);

        return redirect()->route('lp.persetujuan')
            ->with('error', 'Rencana training ditolak.');
    }
}