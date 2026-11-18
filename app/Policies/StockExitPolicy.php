<?php

namespace App\Policies;

use App\Models\StockExit;
use App\Models\User;

class StockExitPolicy
{
    /**
     * Liste globale de toutes les sorties.
     */
    public function viewAny(User $user): bool
    {
        return $user->isResponsable();
    }

    /**
     * Un technicien ne voit que ses propres sorties.
     */
    public function view(User $user, StockExit $exit): bool
    {
        return $user->isResponsable() || $exit->technician_id === $user->id;
    }

    /**
     * Le technicien enregistre lui-même le matériel qu'il prend ;
     * le responsable peut aussi enregistrer une sortie pour un technicien.
     */
    public function create(User $user): bool
    {
        return $user->isResponsable() || $user->isTechnicien();
    }
}
