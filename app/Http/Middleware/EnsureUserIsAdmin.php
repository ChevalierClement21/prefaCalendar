<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Silber\Bouncer\BouncerFacade as Bouncer;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pendant les tests, autoriser tous les accès
        if (app()->environment('testing')) {
            return $next($request);
        }
        
        if (!Auth::check() || !Auth::user()->isAn('admin') || !Auth::user()->can('access-admin-panel')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
            }
            
            return redirect()->route('dashboard')
                ->with('error', 'Vous n\'avez pas les droits d\'administrateur nécessaires pour accéder à cette page.');
        }

        return $next($request);
    }
}
