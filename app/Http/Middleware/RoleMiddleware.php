<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles): Response
    {
        // 1. Cek apakah user login
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // 2. AMBIL SEMUA ROLE USER (MULTI ROLE)
        // Mengambil array nama role, contoh: ['Admin', 'Supervisor']
        $userRoles = $user->roles->pluck('name')->toArray(); 

        // 3. JIKA USER TIDAK PUNYA ROLE SAMA SEKALI
        if (empty($userRoles)) {
            Auth::logout();
            return redirect('login')->with('error', 'Akun Anda tidak memiliki Role yang valid.');
        }

        // --- PERBAIKAN DI SINI ---
        // 4. NORMALISASI ROLE YANG DIIZINKAN (HANDLING SIMBOL PIPA '|')
        // Route::middleware(['role:Admin|Supervisor']) mengirim 'Admin|Supervisor' sebagai satu string
        $rolesToCheck = [];
        foreach ($allowedRoles as $role) {
            // Pecah string jika mengandung '|' (misal: "Admin|Supervisor")
            $parts = explode('|', $role);
            $rolesToCheck = array_merge($rolesToCheck, $parts);
        }

        // 5. CEK APAKAH SALAH SATU ROLE USER BOLEH MASUK?
        foreach ($rolesToCheck as $role) {
            if (in_array($role, $userRoles)) {
                return $next($request);
            }
        }

        // 6. JIKA DITOLAK, LEMPAR KE DASHBOARD SESUAI PRIORITAS
        // Kita cek role tertinggi yang dimiliki user untuk menentukan redirect
        
        $redirectUrl = route('dashboard'); // Default karyawan

        if (in_array('Admin', $userRoles)) {
            $redirectUrl = route('admin.dashboard');
        } elseif (in_array('Supervisor', $userRoles)) {
            $redirectUrl = route('supervisor.dashboard');
        } elseif (in_array('Learning Partner', $userRoles)) {
            $redirectUrl = route('lp.dashboard');
        }

        // Cegah Redirect Loop (Jika url tujuan sama dengan url saat ini)
        if ($request->url() === $redirectUrl) {
             return $next($request); 
        }

        return redirect($redirectUrl)->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}