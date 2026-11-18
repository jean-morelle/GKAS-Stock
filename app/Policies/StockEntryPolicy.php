<?php

namespace App\Policies;

use App\Models\User;

class StockEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isResponsable();
    }

    public function create(User $user): bool
    {
        return $user->isResponsable();
    }
}
