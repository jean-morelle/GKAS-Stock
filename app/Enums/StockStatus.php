<?php

namespace App\Enums;

enum StockStatus: string
{
    case Available = 'disponible';
    case Low = 'faible';
    case OutOfStock = 'rupture';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Low => 'Stock faible',
            self::OutOfStock => 'Rupture',
        };
    }

    /**
     * Classes Tailwind du badge associé au statut.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Available => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::Low => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::OutOfStock => 'bg-red-50 text-red-700 ring-red-600/20',
        };
    }
}
