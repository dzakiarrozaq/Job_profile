<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class RoleSelectionController extends Controller
{
    public function create()
    {
        return view('auth.pilih-peran');
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $user = Auth::user();
        $roleId = $request->input('role_id');

        if (!$user->roles()->where('role_id', $roleId)->exists()) {
            return redirect()->back()->with('error', 'Peran tidak valid.');
        }

        $role = Role::find($roleId);
        $roleName = $role->name;

        $request->session()->put('active_role_name', $roleName);
        $request->session()->put('active_role_id', $roleId);

        $redirectUrl = match ($roleName) {
            'Admin'            => route('admin.dashboard'),
            'Supervisor'       => route('supervisor.dashboard'),
            'Learning Partner' => route('lp.dashboard'),
            default            => route('dashboard'),
        };

        return redirect()->intended($redirectUrl);
    }
}