<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Idp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SupervisorIdpController extends Controller
{
    public function index()
    {
        $subordinates = User::where('manager_id', Auth::id())->pluck('id'); 
        $pendingIdps = Idp::with('user')
                          ->whereIn('user_id', $subordinates)
                          ->where('status', 'submitted')
                          ->latest()
                          ->get();

        return view('supervisor.idp.index', compact('pendingIdps'));
    }

    public function show($id)
    {
        $idp = Idp::with(['user', 'details'])->findOrFail($id);
        
        if ($idp->user->manager_id !== Auth::id()) {
            abort(403, 'Anda bukan supervisor karyawan ini.');
        }

        return view('supervisor.idp.show', compact('idp'));
    }

    public function update(Request $request, $id)
    {
        $idp = Idp::findOrFail($id);

        if ($request->action == 'approve') {
            $idp->update([
                'status' => 'approved',
                'manager_id' => Auth::id(),
                'approved_at' => now(),
            ]);
            $msg = 'IDP berhasil disetujui.';
        } else {
            $idp->update([
                'status' => 'rejected',
                'manager_id' => Auth::id(), 
                'rejection_note' => $request->rejection_note,
            ]);
            $msg = 'IDP ditolak dan dikembalikan ke karyawan.';
        }

        return redirect()->route('supervisor.persetujuan') 
                         ->with('success', $msg);
    }
}