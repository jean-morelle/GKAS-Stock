<?php

namespace App\Enums;

enum UserRole: string
{
    case Responsable = 'responsable';
    case Technicien = 'technicien';

    public function label(): string
    {
        return match ($this) {
            self::Responsable => 'Responsable',
            self::Technicien => 'Technicien',
        };
    }
}
