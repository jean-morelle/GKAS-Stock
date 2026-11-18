<?php

namespace App\Policies;

use App\Models\User;

/**
 * Gestion des comptes techniciens par le responsable.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isResponsable();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isResponsable();
    }

    public function create(User $user): bool
    {
        return $user->isResponsable();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isResponsable() && $model->isTechnicien();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isResponsable() && $model->isTechnicien();
    }
}
