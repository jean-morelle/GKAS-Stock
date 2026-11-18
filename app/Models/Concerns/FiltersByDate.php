<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Filtre par période sur created_at (bornes incluses, jours entiers).
 */
trait FiltersByDate
{
    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): void
    {
        $query
            ->when($from, fn (Builder $q) => $q->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($to, fn (Builder $q) => $q->where('created_at', '<=', Carbon::parse($to)->endOfDay()));
    }
}
