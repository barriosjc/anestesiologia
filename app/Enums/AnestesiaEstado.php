<?php

namespace App\Enums;

enum AnestesiaEstado: string
{
    case Realizado = 'realizado';
    case Suspendido = 'suspendido';

    public function label(): string
    {
        return match ($this) {
            self::Realizado => 'Realizado',
            self::Suspendido => 'Suspendido',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Realizado => 'bg-success text-white',
            self::Suspendido => 'bg-warning text-dark',
        };
    }
}
