<?php

namespace App\Repositories;

use App\Models\GuardiaMedico;
use App\Models\Profesional;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GuardiaMedicoRepository
{
    private const PALETA = [
        '#1782e0', '#2ab8e8', '#0f9d8f', '#7a5cff', '#e67e22',
        '#c0392b', '#16a085', '#8e44ad', '#2c3e50', '#d35400',
        '#27ae60', '#7f8c8d', '#f39c12', '#1abc9c', '#e74c3c',
    ];

    public function medicos(): Collection
    {
        return Profesional::orderBy('nombre')->get();
    }

    public function colorPara(int $medicoId): string
    {
        return self::PALETA[$medicoId % count(self::PALETA)];
    }

    public function mes(int $anio, int $mes): Collection
    {
        $inicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();

        return GuardiaMedico::with('medico')
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->get()
            ->keyBy(fn (GuardiaMedico $g) => $g->fecha->format('Y-m-d'));
    }

    /**
     * Guarda una asignación (fecha -> medico_id) en la tabla guardias_medicos.
     */
    public function confirmar(array $asignaciones, FeriadoRepository $feriadoRepository): int
    {
        $total = 0;

        foreach ($asignaciones as $fecha => $medicoId) {
            $dia = Carbon::parse($fecha);
            $row = GuardiaMedico::firstOrNew(['fecha' => $fecha]);
            $row->medico_id = (int) $medicoId;
            $row->es_sabado = $dia->isSaturday() ? 1 : 0;
            $row->es_domingo = $dia->isSunday() ? 1 : 0;
            $row->feriado = $feriadoRepository->nombre($fecha);
            $row->color = $this->colorPara((int) $medicoId);
            $row->save();
            $total++;
        }

        return $total;
    }

    public function quitarDia(string $fecha): void
    {
        GuardiaMedico::where('fecha', $fecha)->delete();
    }
}