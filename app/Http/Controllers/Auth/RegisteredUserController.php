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
        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
        ]);

        // -------------------------------------------------------------
        // 2. LOGIC PENCARIAN SUPERVISOR OTOMATIS (PINDAHKAN KE SINI)
        // -------------------------------------------------------------
        $managerId = null;
        
        // Cari data posisi yang dipilih user
        $position = Position::find($request->position_id);

        // Cek apakah posisi tersebut punya atasan (atasan_id tidak null)
        if ($position && $position->atasan_id) {
            // Cari USER real yang sedang menjabat di posisi atasan tersebut
            $manager = User::where('position_id', $position->atasan_id)->first();
            
            if ($manager) {
                $managerId = $manager->id;
            }
        }
        // -------------------------------------------------------------

        // 3. Simpan User Baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
            'department_id' => $request->department_id,
            'position_id' => $request->position_id,
            'manager_id' => $managerId, // <--- MASUKKAN HASIL PENCARIAN DI ATAS
        ]);

        // 4. Assign Role (Contoh: Default Karyawan Outsourcing / Organik)
        // Sesuaikan nama role dengan database Anda
        $roleName = 'Karyawan Outsourcing'; // Atau logika lain
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