<?php

namespace App\Enums;

enum ProductUnit: string
{
    case Unit = 'unité';
    case Piece = 'pièce';
    case Meter = 'mètre';
    case Roll = 'rouleau';
    case Box = 'boîte';
    case Carton = 'carton';
    case Pack = 'paquet';
    case Liter = 'litre';
    case Kilogram = 'kg';

    /**
     * Abréviation affichée à côté des quantités.
     */
    public function short(): string
    {
        return match ($this) {
            self::Meter => 'm',
            self::Liter => 'L',
            self::Kilogram => 'kg',
            default => $this->value,
        };
    }

    /**
     * Quantité formatée avec l'unité (ex. « 1 250 m »).
     */
    public function format(int $quantity): string
    {
        return number_format($quantity, 0, ',', ' ').' '.$this->short();
    }
}
