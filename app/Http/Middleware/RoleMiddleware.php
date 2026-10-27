<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role !== $role) {
            // Redirect based on their role
            if (auth()->user()->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard')->with('error', 'Akses ditolak.');
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        return $next($request);
    }
}
