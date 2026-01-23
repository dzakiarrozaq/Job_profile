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
        AuditLog::record('Login', 'Pengguna berhasil login.');

        $user = Auth::user();

        // JIKA PUNYA BANYAK ROLE -> ARAHKAN KE HALAMAN PILIH PERAN
        if ($user->roles->count() > 1) {
            return redirect()->route('role.selection');
        }

        // JIKA CUMA 1 ROLE -> LANGSUNG MASUK
        $roleName = $user->roles->first()?->name ?? 'User';
        
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
        if (Auth::check()) {
            AuditLog::record('Logout', 'Pengguna keluar dari sistem.');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}