<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSelectionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->roles->count() === 1) {
            return $this->redirectBasedOnRole($user->roles->first()->name);
        }
        return view('auth.pilih-peran', ['roles' => $user->roles]);
    }

    public function select(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|exists:roles,name'
        ]);

        $user = Auth::user();

        if (!$user->roles->contains('name', $request->role_name)) {
            abort(403, 'Anda tidak memiliki hak akses untuk role ini.');
        }

        session(['active_role' => $request->role_name]);

        $tujuan = match ($request->role_name) {
            'Admin'            => 'admin.dashboard',
            'Supervisor'       => 'supervisor.dashboard', 
            'Learning Partner' => 'lp.dashboard',
            default            => 'dashboard',
        };

        
        if (!\Illuminate\Support\Facades\Route::has($tujuan)) {
            dd("EROR: Route dengan nama '$tujuan' TIDAK DITEMUKAN di web.php. Silakan cek file routes/web.php Anda.");
        }

        return redirect()->route($tujuan);
    }

    private function redirectBasedOnRole($roleName)
    {
        $route = match ($roleName) {
            'Admin'            => 'admin.dashboard',
            'Supervisor'       => 'supervisor.dashboard',
            'Learning Partner' => 'lp.dashboard',
            default            => 'dashboard',
        };
        return redirect()->route($route);
    }
}