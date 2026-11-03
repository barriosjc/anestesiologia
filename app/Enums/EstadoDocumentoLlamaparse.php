<?php

namespace App\Enums;

enum EstadoDocumentoLlamaparse: string
{
    case Pendiente = 'pendiente';
    case Procesando = 'procesando';
    case Procesado = 'procesado';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Procesando => 'Procesando',
            self::Procesado => 'Procesado',
            self::Error => 'Error',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pendiente => 'bg-secondary text-white',
            self::Procesando => 'bg-info text-dark',
            self::Procesado => 'bg-success text-white',
            self::Error => 'bg-dark text-white',
        };
    }

    public function enProceso(): bool
    {
        return in_array($this, [self::Pendiente, self::Procesando], true);
    }
}
