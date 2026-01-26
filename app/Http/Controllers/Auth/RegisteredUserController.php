<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
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
    public function create(): View
    {
        // 1. Ambil semua organisasi
        $allOrgs = Organization::with('positions')->get();

        $sortedOrganizations = collect();
        
        // PERBAIKAN: Hapus parameter $prefix karena tidak dibutuhkan lagi untuk tampilan
        $sortRecursive = function ($parentId = null) use ($allOrgs, &$sortedOrganizations, &$sortRecursive) {
            
            $children = $allOrgs->where('parent_id', $parentId)->sortBy('name');

            foreach ($children as $child) {
                // PERBAIKAN DI SINI:
                // Hapus "$prefix ." di depan nama.
                // Hasilnya nama bersih tanpa tanda strip, tapi urutan tetap Hierarkis (Dept -> Section -> Unit)
                $child->display_name = $child->name . ' (' . ucfirst($child->type) . ')';
                
                $sortedOrganizations->push($child);

                // Panggil rekursif untuk anak-anaknya
                $sortRecursive($child->id); 
            }
        };

        // Mulai dari root
        $sortRecursive(null);

        return view('auth.register', [
            'organizations' => $sortedOrganizations,
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
            'organization_id' => ['required', 'exists:organizations,id'], // PERBAIKAN: Validasi ke organizations
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
            'position_id' => $request->position_id,
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