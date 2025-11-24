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
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        $activeRoleName = $request->session()->get('active_role_name');

        if (empty($activeRoleName)) {
            $user->load('roles');
            
            if ($user->roles->count() > 1) {
                return redirect()->route('role.selection');
            
            } elseif ($user->roles->count() === 1) {
                $roleName = $user->roles->first()->name;
                $request->session()->put('active_role_name', $roleName);
                $activeRoleName = $roleName; 
            } else {
                Auth::logout();
                return redirect('login');
            }
        }
        if (in_array($activeRoleName, $roles)) {
            return $next($request);
        }

        $redirectUrl = match ($activeRoleName) {
            'Admin'            => route('admin.dashboard'),
            'Supervisor'       => route('supervisor.dashboard'),
            'Learning Partner' => route('lp.dashboard'),
            default            => route('dashboard'),
        };

        if ($request->url() === $redirectUrl) {
             return $next($request); 
        }

        return redirect($redirectUrl);
    }
}