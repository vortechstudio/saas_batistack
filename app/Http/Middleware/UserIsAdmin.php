<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $emails): Response
    {
        // 1. Vérifier si l'utilisateur est connecté
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // 2. Récupérer la liste des emails autorisés (paramètre du middleware)
        $allowedEmails = array_map('trim', explode(',', $emails));

        // 3. Vérifier si l'email de l'utilisateur est dans la liste
        if (! in_array($request->user()->email, $allowedEmails)) {
            // Si ce n'est pas le bon email, on renvoie une 403 (Interdit)
            abort(403, 'Accès non autorisé. Votre adresse email n\'a pas les permissions nécessaires.');
        }

        return $next($request);
    }
}
