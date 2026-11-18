<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Les techniciens peuvent consulter le catalogue et les quantités disponibles.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * La fiche produit expose l'historique de tous les techniciens : réservée au responsable.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->isResponsable();
    }

    public function viewAlerts(User $user): bool
    {
        return $user->isResponsable();
    }

    public function create(User $user): bool
    {
        return $user->isResponsable();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isResponsable();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isResponsable();
    }
}
