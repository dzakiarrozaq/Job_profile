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
use App\Models\Role; // Benar: Singular
use App\Models\Position;
use App\Models\Organization;
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
        
        // Pastikan supervisor punya posisi
        if (!$supervisor->position) {
            return view('supervisor.tim.index', [
                'teamMembers' => collect([]), 'totalCount' => 0, 'organicCount' => 0, 
                'outsourcingCount' => 0, 'roles' => Role::all()
            ]);
        }

        // AMBIL SEMUA ID POSISI BAWAHAN (SM, AM, Officer, dst)
        $allSubordinatePositionIds = $supervisor->position->getAllSubordinateIds();

        // Query User yang menduduki posisi-posisi tersebut
        $baseQuery = User::whereIn('position_id', $allSubordinatePositionIds)
                        ->with(['position.organization', 'roles', 'employeeProfile']);

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        // Filter Role
        if ($request->filled('roles') && $request->roles != 'all') {
            $baseQuery->whereHas('roles', fn($q) => $q->where('name', $request->roles));
        }

        $teamMembers = $baseQuery->paginate(10);
        
        // Statistik menggunakan list ID yang sama
        $totalCount = User::whereIn('position_id', $allSubordinatePositionIds)->count();
        $organicCount = User::whereIn('position_id', $allSubordinatePositionIds)
                            ->whereHas('roles', fn($q) => $q->where('name', 'Karyawan Organik'))->count();
        $outsourcingCount = User::whereIn('position_id', $allSubordinatePositionIds)
                                ->whereHas('roles', fn($q) => $q->where('name', 'Karyawan Outsourcing'))->count();

        // Mapping Status Assessment
        foreach ($teamMembers as $member) {
            $member->assessment_status = $member->employeeProfile->status ?? 'not_started';
        }

        return view('supervisor.tim.index', [
            'teamMembers' => $teamMembers,
            'filters' => $request->all(),
            'totalCount' => $totalCount,           
            'organicCount' => $organicCount,       
            'outsourcingCount' => $outsourcingCount,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Menampilkan detail profil anggota tim.
     */
    public function show($id): View
    {
        $supervisor = Auth::user();
        $user = User::with('position')->findOrFail($id);

        // Cek apakah posisi user tersebut masuk dalam daftar hirarki bawahan supervisor
        $allSubordinatePositionIds = $supervisor->position->getAllSubordinateIds();
        
        if (!in_array($user->position_id, $allSubordinatePositionIds)) {
            abort(403, 'Anda tidak memiliki akses ke profil anggota tim di luar hirarki Anda.');
        }

        $user->load(['position.organization', 'position.jobGrade', 'jobHistories', 'educationHistories', 'skills']);
        $gapRecords = GapRecord::where('gap_records.user_id', $user->id)
            ->leftJoin('competencies_master', 'gap_records.competency_name', '=', 'competencies_master.competency_name')
            ->select('gap_records.*', 'competencies_master.type') // Ambil type dari master
            ->orderBy('gap_value', 'asc')
            ->get();
        
        $activePlans = TrainingPlan::where('user_id', $user->id)
                        ->whereIn('status', ['pending_supervisor', 'pending_lp', 'approved'])
                        ->with('items.training')->orderBy('created_at', 'desc')->get();

        $completedHistory = TrainingPlan::where('user_id', $user->id)
                        ->whereIn('status', ['completed', 'rejected'])
                        ->with('items.training')->orderBy('updated_at', 'desc')->get();

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
        $supervisorPositionId = Auth::user()->position_id;
        
        $positions = Position::where('atasan_id', $supervisorPositionId)
                        ->orderBy('title', 'asc')
                        ->get();

        $organizations = Organization::orderBy('name', 'asc')->get();
        
        // Perbaikan: Gunakan Role::whereIn (Singular)
        $roles = Role::whereIn('name', ['Karyawan Organik', 'Karyawan Outsourcing'])->get();

        return view('supervisor.tim.create', [
            'positions' => $positions,
            'organizations' => $organizations,
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
            'nik' => ['nullable', 'string', 'max:50'],
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
                'role_id' => $request->role_id,
                'position_id' => $request->position_id,
                'nik' => $request->nik,
                'status' => 'active',
                'gender' => $request->gender,
                'profile_photo_path' => $photoPath,
                'phone_number' => $request->phone_number,
                'hiring_date' => $request->hiring_date,
            ]);

            // Jika menggunakan pivot table (roles jamak), aktifkan baris ini:
            $user->roles()->attach($request->role_id); 
        });

        return redirect()->route('supervisor.tim.index')->with('success', 'Anggota tim baru berhasil ditambahkan.');
    }
}