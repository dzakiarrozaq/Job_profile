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
                ->orWhere('email', 'like', "%{$search}%");
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
            'organizations' => Organization::orderBy('name')->get(), 
            'positions' => Position::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_ids' => ['required', 'array'], // Wajib Array
            'role_ids.*' => ['exists:roles,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
        ]);

        // 1. Buat User (Tanpa kolom role_id)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'position_id' => $request->position_id,
            // 'role_id' => ... HAPUS INI, karena sudah pakai tabel pivot
            'status' => 'active',
        ]);

        // 2. Simpan Role ke tabel role_user
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
            // JANGAN update 'role_id' di sini
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        // 1. Update Data User
        $user->update($dataToUpdate);

        // 2. Update Role di tabel role_user (Multi Role)
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
}