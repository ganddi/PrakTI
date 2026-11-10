<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role-based access control.
 * Pemakaian di route: ->middleware('role:admin') atau ->middleware('role:admin,pembimbing')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Akun yang dinonaktifkan admin langsung dikeluarkan dari sesi
        if (! $user || ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda belum aktif atau telah dinonaktifkan oleh admin program studi.']);
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Halaman ini tidak tersedia untuk peran Anda.');
        }

        return $next($request);
    }
}
