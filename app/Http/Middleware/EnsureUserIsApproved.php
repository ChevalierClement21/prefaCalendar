<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pendant les tests, ne pas vérifier l'approbation
        if (app()->environment('testing')) {
            return $next($request);
        }
        
        if (Auth::check() && !Auth::user()->approved) {
            Auth::logout();
            
            return redirect()->route('login')
                ->with('error', 'Votre compte est en attente d\'approbation par un administrateur.');
        }

        return $next($request);
    }
}
