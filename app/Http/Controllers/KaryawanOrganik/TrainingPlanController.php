<?php

namespace App\Http\Controllers\KaryawanOrganik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TrainingPlan;
use App\Models\TrainingPlanItem;
use App\Models\Training;

class TrainingPlanController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $plans = TrainingPlan::with(['items.training']) 
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('karyawan.rencana.index', compact('plans'));
    }

    /**
     * Langsung simpan ke keranjang tanpa form perantara
     */
    public function store(Request $request)
    {
        $request->validate([
            'training_id' => 'required|exists:trainings,id', 
        ]);

        $training = Training::find($request->training_id);

        $plan = TrainingPlan::create([
            'user_id' => Auth::id(),
            'status' => 'pending_supervisor', 
            'submitted_at' => now(),
        ]);

        
        TrainingPlanItem::create([
            'training_plan_id' => $plan->id,
            'training_id' => $training->id,
            
            'title' => $training->title,
            'provider' => $training->provider,
            'method' => $training->method ?? 'Offline',
            'cost' => $training->cost ?? 0,
            
        ]);

        return redirect()->back() 
            ->with('success', 'Pelatihan "' . $training->title . '" berhasil dimasukkan ke Rencana.');
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

        if (in_array($plan->status, ['approved', 'completed'])) {
            return back()->with('error', 'Rencana yang sudah Disetujui tidak dapat dihapus.');
        }

        $plan->delete();

        return back()->with('success', 'Rencana pelatihan berhasil dihapus.');
    }
}