<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog; 
use Maatwebsite\Excel\Facades\Excel; // Tambahkan ini
use App\Imports\UsersImport; // Tambahkan ini (Pastikan file Import sudah dibuat)

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Eager load 'roles' (jamak)
        $query = User::with(['roles', 'position.organization']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%"); // Tambahkan pencarian NIK jika ada
            });
        }

        if ($request->filled('roles') && $request->roles !== 'all') {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->roles);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all(); 

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => Role::all(),
            'organizations' => Organization::orderBy('name', 'ASC')->get(),
            'positions' => Position::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_ids' => ['required', 'array'], 
            'role_ids.*' => ['exists:roles,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
        ]);

        // 1. Buat User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'position_id' => $request->position_id,
            'status' => 'active',
        ]);

        // 2. Simpan Role
        $user->roles()->sync($request->role_ids);

        AuditLog::record('Create User', "Menambahkan pengguna baru: {$user->name} ({$user->email})", $user);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user, 
            'roles' => Role::all(),
            'organizations' => Organization::orderBy('name')->get(),
            'positions' => Position::orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['exists:roles,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
        ]);

        $dataToUpdate = [
            'name' => $request->name,
            'email' => $request->email,
            'position_id' => $request->position_id,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        $user->update($dataToUpdate);
        $user->roles()->sync($request->role_ids);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        
        $userName = $user->name;
        $user->delete();

        AuditLog::record('Delete User', "Menghapus pengguna: {$userName} (ID: {$user->id})", $user);
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    // --- BARU: Method Import Excel ---
    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        // --- TAMBAHKAN BARIS INI ---
        // Set timeout menjadi 10 menit (600 detik) atau 0 (unlimited)
        // Agar proses import 5000 data tidak terputus di tengah jalan.
        set_time_limit(0); 
        // ---------------------------

        try {
            Excel::import(new UsersImport, $request->file('file'));
            
            AuditLog::record('Import User', "Melakukan import data pengguna dari Excel", Auth::user());

            return back()->with('success', 'Data pengguna berhasil diimport! Silakan cek dan lengkapi role user baru.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    // --- BARU: Method Download Template ---
    public function downloadTemplate()
    {
        // Pastikan Anda sudah membuat file excel template di folder public/templates
        $path = public_path('templates/template_import_user.xlsx');
        
        if (!file_exists($path)) {
            // Jika file fisik belum ada, kita bisa generate on-the-fly (opsional) atau return error
            return back()->with('error', 'File template belum tersedia di server.');
        }

        return response()->download($path);
    }
}