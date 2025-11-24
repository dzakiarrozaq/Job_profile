<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\AuditLog;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        AuditLog::record('Login', 'Pengguna berhasil login ke dalam sistem.');

        $user = Auth::user();
        $user->load('roles'); // Load semua peran

        // Skenario 1: User tidak punya role sama sekali
        if ($user->roles->count() === 0) {
            Auth::logout();
            return redirect('login')->with('error', 'Akun Anda tidak memiliki akses peran.');
        }

        // Skenario 2: User punya BANYAK role (Multi-Role)
        // -> Arahkan ke halaman "Pilih Peran"
        if ($user->roles->count() > 1) {
            return redirect()->route('role.selection');
        }

        // Skenario 3: User hanya punya 1 role (Single-Role)
        // -> Langsung set session dan masuk dashboard
        $roleName = $user->roles->first()->name;
        $request->session()->put('active_role_name', $roleName);

        $redirectUrl = match ($roleName) {
            'Admin'            => route('admin.dashboard'),
            'Supervisor'       => route('supervisor.dashboard'),
            'Learning Partner' => route('lp.dashboard'),
            default            => route('dashboard'),
        };

        return redirect()->intended($redirectUrl);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
