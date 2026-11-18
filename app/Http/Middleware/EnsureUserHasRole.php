<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage : ->middleware('role:responsable') ou 'role:responsable,technicien'.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowed = array_map(fn (string $role) => UserRole::from($role), $roles);

        abort_unless($user && in_array($user->role, $allowed, true), 403, 'Accès réservé.');

        return $next($request);
    }
}
