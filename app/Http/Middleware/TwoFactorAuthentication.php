<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Si l'utilisateur a activé la 2FA mais n'a pas confirmé son code dans cette session
        if ($user->hasTwoFactorEnabled() && !session('2fa_verified')) {
            // Exclure les routes de 2FA pour éviter une boucle infinie
            if (!$request->routeIs('two-factor.*')) {
                return redirect()->route('two-factor.verify');
            }
        }

        return $next($request);
    }
}
