<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingPlanItem;
use App\Models\AuditLog; 
use Illuminate\Support\Facades\Auth;
use App\Notifications\StatusDiperbarui;

class CertificateController extends Controller
{
    public function index()
    {
        $items = TrainingPlanItem::whereHas('plan.user', function($q) {
                $q->where('manager_id', Auth::id());
            })
            ->where('certificate_status', 'pending_approval')
            ->with(['plan.user', 'training'])
            ->latest()
            ->paginate(10);

        return view('supervisor.sertifikat.index', compact('items'));
    }

    public function approve($id)
    {
        $item = TrainingPlanItem::findOrFail($id);
        
        $item->update([
            'certificate_status' => 'verified'
        ]);

        $item->plan->update([
            'status' => 'completed',
            'completed_at' => now() 
        ]);

        AuditLog::record('VERIFY CERTIFICATE', 'Memvalidasi kelulusan pelatihan: ' . $item->title, $item->plan);

        $item->plan->user->notify(new StatusDiperbarui(
            'Sertifikat Valid', 
            'Selamat! Sertifikat pelatihan "' . $item->title . '" telah divalidasi. Status pelatihan kini Selesai.', 
            route('riwayat'), 
            'success' 
        ));

        return back()->with('success', 'Sertifikat divalidasi. Pelatihan dinyatakan SELESAI.');
    }

    public function reject(Request $request, $id)
    {
        $item = TrainingPlanItem::findOrFail($id);
        
        $item->update([
            'certificate_status' => 'rejected'
        ]);

        AuditLog::record('REJECT CERTIFICATE', 'Menolak sertifikat pelatihan: ' . $item->title, $item->plan);

        $item->plan->user->notify(new StatusDiperbarui(
            'Sertifikat Ditolak', 
            'Maaf, sertifikat pelatihan "' . $item->title . '" ditolak. Mohon periksa kembali file Anda dan upload ulang.', 
            route('rencana.sertifikat', $item->id), 
            'error' 
        ));

        return back()->with('error', 'Sertifikat ditolak. Karyawan diminta upload ulang.');
    }
}