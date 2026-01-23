<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Di sini HANYA ambil data untuk dropdown
        $departments = Department::with('positions')
                             ->orderBy('name', 'asc')
                             ->get();

        return view('auth.register', [
            'departments' => $departments,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'company_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'nik' => ['required', 'string', 'max:50'],
        ]);

        
        $managerId = null;
        
        $position = Position::find($request->position_id);

        if ($position && $position->atasan_id) {
            $manager = User::where('position_id', $position->atasan_id)->first();
            
            if ($manager) {
                $managerId = $manager->id;
            }
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'company_name' => $request->company_name,
            'password' => Hash::make($request->password),
            'status' => 'active',
            'department_id' => $request->department_id,
            'position_id' => $request->position_id,
            'manager_id' => $managerId,
            'nik' => $request->nik,
        ]);

        
        $roleName = 'Karyawan Outsourcing';
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $user->roles()->attach($role->id);
        }

        event(new Registered($user));

        Auth::login($user);

        // Set session role
        $request->session()->put('active_role_name', $roleName);

        return redirect(route('dashboard', absolute: false));
    }
}