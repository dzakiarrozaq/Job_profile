<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // Daftarkan alias 'role' middleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // ✅ PERBAIKAN: Redirect guest ke login
        // Ini untuk user yang BELUM login mencoba akses halaman yang butuh auth
        $middleware->redirectGuestsTo(fn() => route('login'));

        $middleware->redirectUsersTo('/dashboard');
        
        // $middleware->redirectUsersTo(fn() => '/dashboard');

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();