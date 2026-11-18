<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mémorise l'activité de l'utilisateur pour afficher « Depuis votre dernière visite ».
 *
 * Une nouvelle visite commence après une inactivité de plus de
 * config('stock.visit_gap_minutes') : la fin de la visite précédente
 * est alors conservée dans previous_visit_at.
 */
class TrackLastVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $this->track($user);
        }

        return $next($request);
    }

    private function track(User $user): void
    {
        // Instance créée en mémoire (ex. tests) : colonnes de suivi non chargées.
        if (! array_key_exists('last_seen_at', $user->getAttributes())) {
            $user->refresh();
        }

        $now = now();
        $lastSeen = $user->last_seen_at;

        // Une écriture par minute au plus.
        if ($lastSeen && $lastSeen->gt($now->copy()->subMinute())) {
            return;
        }

        $changes = ['last_seen_at' => $now];

        if (! $lastSeen || $lastSeen->lt($now->copy()->subMinutes(config('stock.visit_gap_minutes')))) {
            $changes['previous_visit_at'] = $lastSeen;
        }

        // Mise à jour directe : ne modifie pas updated_at.
        User::whereKey($user->getKey())->toBase()->update($changes);
        $user->forceFill($changes)->syncOriginal();
    }
}
