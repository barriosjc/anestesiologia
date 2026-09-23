<?php

namespace App\Repositories;

use App\Models\Feriado;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FeriadoRepository
{
    public function mes(int $anio, int $mes): Collection
    {
        $inicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();

        return Feriado::whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->get()
            ->keyBy(fn (Feriado $f) => $f->fecha->format('Y-m-d'));
    }

    public function fechasDelMes(int $anio, int $mes): array
    {
        return $this->mes($anio, $mes)->keys()->all();
    }

    public function nombre(string $fecha): ?string
    {
        return Feriado::where('fecha', $fecha)->value('nombre');
    }

    public function tiene(string $fecha): bool
    {
        return Feriado::where('fecha', $fecha)->exists();
    }

    public function crear(string $fecha, string $nombre): void
    {
        Feriado::firstOrCreate(
            ['fecha' => $fecha],
            ['nombre' => $nombre]
        );
    }
}