<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; 
use App\Models\User;
use App\Models\Role;
use App\Models\Position;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\GapRecord;
use App\Models\TrainingPlan;

class TeamController extends Controller
{
    /**
     * Menampilkan daftar semua anggota tim.
     */
    public function index(Request $request): View
    {
        $supervisor = Auth::user();
        
        $baseQuery = User::where('manager_id', $supervisor->id)
                         ->with(['position', 'department', 'roles']); 

        $allMembers = $baseQuery->get(); 
        
        $organicCount = $allMembers->filter(function($user) {
            return $user->roles->contains('name', 'Karyawan Organik');
        })->count();

        $outsourcingCount = $allMembers->filter(function($user) {
            return $user->roles->contains('name', 'Karyawan Outsourcing');
        })->count();

        $totalCount = $allMembers->count();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->role != 'all') {
            $baseQuery->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $teamMembers = $baseQuery->paginate(10);

        foreach ($teamMembers as $member) {
            $latestAssessment = EmployeeProfile::where('user_id', $member->id)
                    ->orderBy('submitted_at', 'desc')
                    ->first();
            
            if (!$latestAssessment) {
                $member->assessment_status = 'not_started'; 
            } else {
                $member->assessment_status = $latestAssessment->status;
            }
        }

        return view('supervisor.tim.index', [
            'teamMembers' => $teamMembers,
            'filters' => $request->all(),
            'totalCount' => $totalCount,           
            'organicCount' => $organicCount,       
            'outsourcingCount' => $outsourcingCount 
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

        $gapRecords = GapRecord::where('user_id', $user->id)
                        ->orderBy('gap_value', 'asc')
                        ->get();

        $activePlans = TrainingPlan::where('user_id', $user->id)
                        ->whereIn('status', ['pending_supervisor', 'pending_lp', 'approved'])
                        ->with('items.training')
                        ->orderBy('created_at', 'desc')
                        ->get();

        $completedHistory = TrainingPlan::where('user_id', $user->id)
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

    /**
     * Menampilkan form tambah anggota tim.
     */
    public function create(): View
    {
        $positions = Position::orderBy('title', 'asc')->get();
        $departments = Department::orderBy('name', 'asc')->get();
        
        $roles = Role::whereIn('name', ['Karyawan Organik', 'Karyawan Outsourcing'])->get();

        return view('supervisor.tim.create', [
            'positions' => $positions,
            'departments' => $departments,
            'roles' => $roles,
        ]);
    }

    /**
     * Menyimpan anggota tim baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'], 
            'role_id' => ['required', 'exists:roles,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'batch_number' => ['nullable', 'string', 'max:50'],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'profile_photo' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png'], 
            'phone_number' => ['nullable', 'string', 'max:20'],
            'hiring_date' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($request) {
            
            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password123'), 
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'batch_number' => $request->batch_number,
                'manager_id' => Auth::id(), 
                'status' => 'active',
                'gender' => $request->gender,
                'profile_photo_path' => $photoPath,
                'phone_number' => $request->phone_number,
                'hiring_date' => $request->hiring_date,
            ]);

            $user->roles()->attach($request->role_id);
            
            
        });

        return redirect()->route('supervisor.tim.index')->with('success', 'Anggota tim baru berhasil ditambahkan.');
    }
}