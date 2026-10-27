<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Hanya cek untuk tenant
        if ($user && $user->role === 'tenant') {
            
            // Bypass halaman expired agar tidak terjadi redirect loop
            if ($request->routeIs('subscription.expired')) {
                return $next($request);
            }
            
            // Jika waktu sekarang melebihi batas waktu langganan
            if ($user->package_expires_at && now()->greaterThan($user->package_expires_at)) {
                return redirect()->route('subscription.expired');
            }
        }

        return $next($request);
    }
}
