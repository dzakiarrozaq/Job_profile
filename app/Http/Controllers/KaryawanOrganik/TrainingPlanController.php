<?php

namespace App\Http\Controllers\KaryawanOrganik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TrainingPlan;
use App\Models\TrainingPlanItem;
use App\Models\Training;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;

class TrainingPlanController extends Controller
{
    public function index()
    {        
        $userId = Auth::id();
        $plans = TrainingPlan::with(['items.training']) 
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $hasDrafts = TrainingPlan::where('user_id', $userId)
                        ->where('status', 'draft')
                        ->exists();

        return view('karyawan.rencana.index', compact('plans', 'hasDrafts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'training_id' => 'required|exists:trainings,id', 
        ]);

        $training = Training::find($request->training_id);

        $plan = TrainingPlan::create([
            'user_id' => Auth::id(),
            'status' => 'draft',
            'submitted_at' => null, 
        ]);
        
        AuditLog::record('CREATE DRAFT', 'Membuat draft rencana: ' . $training->title, $plan);

        TrainingPlanItem::create([
            'training_plan_id' => $plan->id,
            'training_id' => $training->id,
            'title' => $training->title,
            'provider' => $training->provider,
            'method' => $training->method ?? 'Offline',
            'cost' => $training->cost ?? 0,
        ]);

        return redirect()->back() 
            ->with('success', 'Pelatihan dimasukkan ke keranjang (Draft). Jangan lupa ajukan nanti!');
    }

    /**
     * BARU: Method untuk Mengajukan Semua Draft ke Supervisor
     */
    public function submitAll()
    {
        $affected = TrainingPlan::where('user_id', Auth::id())
            ->where('status', 'draft')
            ->update([
                'status' => 'pending_supervisor',
                'submitted_at' => now(), 
            ]);

        if ($affected > 0) {
            AuditLog::record('SUBMIT PLAN', 'Mengajukan ' . $affected . ' rencana ke Supervisor');
            return redirect()->route('rencana.index')
                ->with('success', 'Berhasil mengajukan ' . $affected . ' rencana pelatihan ke Supervisor.');
        }

        return redirect()->back()->with('error', 'Tidak ada item di keranjang untuk diajukan.');
    }

    public function formSertifikat($itemId)
    {
        $item = TrainingPlanItem::whereHas('plan', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($itemId);

        return view('karyawan.rencana.upload-sertifikat', compact('item'));
    }

    /**
     * Proses Simpan Sertifikat (SECURE VERSION)
     */
    public function storeSertifikat(Request $request, $itemId)
    {
        $request->validate([
            'file' => [
                'required',                 
                'file',                    
                'mimes:pdf,jpg,jpeg,png',   
                'max:2048',                 
            ],
        ]);

        $item = TrainingPlanItem::whereHas('plan', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($itemId);

        if ($request->hasFile('file')) {
            try {
                if ($item->certificate_path && Storage::disk('public')->exists($item->certificate_path)) {
                    Storage::disk('public')->delete($item->certificate_path);
                }

                $path = $request->file('file')->store('certificates', 'public');
                
                $item->update([
                    'certificate_path' => $path,
                    'certificate_status' => 'pending_approval' 
                ]);

                AuditLog::record('UPLOAD CERTIFICATE', 'Mengunggah sertifikat baru', $item->plan);

            } catch (\Exception $e) {
                return back()->with('error', 'Terjadi kesalahan saat mengunggah file. Silakan coba lagi.');
            }
        }

        return back()->with('success', 'Sertifikat berhasil diunggah. Mohon tunggu verifikasi Supervisor.');
    }

    public function show($id)
    {
        $plan = TrainingPlan::with(['items.training'])
            ->where('user_id', Auth::id()) 
            ->findOrFail($id);

        return view('karyawan.rencana.show', compact('plan'));
    }

    /**
     * Menghapus Rencana (Hanya jika status masih pending)
     */
    public function destroy($id)
    {
        $plan = TrainingPlan::where('user_id', Auth::id())->findOrFail($id);
        AuditLog::record('DELETE PLAN', 'Menghapus rencana pelatihan ID: ' . $id);

        if (in_array($plan->status, ['approved', 'completed'])) {
            return back()->with('error', 'Rencana yang sudah Disetujui tidak dapat dihapus.');
        }

        $plan->delete();

        return back()->with('success', 'Rencana pelatihan berhasil dihapus.');
    }
}