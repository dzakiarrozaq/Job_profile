<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\EmployeeProfile;

class TeamController extends Controller
{
    /**
     * Menampilkan daftar semua anggota tim.
     */
    public function index(Request $request): View
    {
        $supervisor = Auth::user();
        
        // Query dasar: Ambil user yang manager_id-nya adalah supervisor ini
        $query = User::where('manager_id', $supervisor->id)
                     ->with(['position', 'department', 'roles']); // Eager load

        // Fitur Pencarian (Opsional)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Fitur Filter Role (Opsional)
        if ($request->has('role') && $request->role != 'all') {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $teamMembers = $query->paginate(10); // Pagination

        // Tambahkan status penilaian ke setiap member untuk ditampilkan di tabel
        foreach ($teamMembers as $member) {
            // Cek status penilaian terakhir
            $latestAssessment = EmployeeProfile::where('user_id', $member->id)
                                    ->orderBy('submitted_at', 'desc')
                                    ->first();
            
            $member->assessment_status = $latestAssessment ? $latestAssessment->status : 'belum_mulai';
        }

        return view('supervisor.tim.index', [
            'teamMembers' => $teamMembers,
            'filters' => $request->all()
        ]);
    }

    /**
     * Menampilkan detail profil anggota tim.
     */
    public function show(User $user): View
    {
        if ($user->manager_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke profil karyawan ini.');
        }

        $user->load(['position.department', 'position.jobGrade', 'jobHistories', 'educationHistories', 'skills']);

        $gapRecords = \App\Models\GapRecord::where('user_id', $user->id)
                        ->orderBy('gap_value', 'asc')
                        ->get();

        $activePlans = \App\Models\TrainingPlan::where('user_id', $user->id)
                        ->whereIn('status', ['pending_supervisor', 'pending_lp', 'approved'])
                        ->with('items.training')
                        ->orderBy('created_at', 'desc')
                        ->get();

        $completedHistory = \App\Models\TrainingPlan::where('user_id', $user->id)
                        ->whereIn('status', ['completed', 'rejected'])
                        ->with('items.training')
                        ->orderBy('updated_at', 'desc')
                        ->get();

        return view('supervisor.tim.show', [
            'employee' => $user,
            'gapRecords' => $gapRecords,
            'activePlans' => $activePlans,
            'completedHistory' => $completedHistory
        ]);
    }
}