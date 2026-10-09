<?php

namespace App\Repositories;

use App\Models\GuardiaMedico;
use App\Models\Profesional;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GuardiaMedicoRepository
{
    public function medicos(): Collection
    {
        return Profesional::orderBy('nombre')->get();
    }

    /**
     * Paleta fija de 15 colores bien diferenciados. Se reparte de forma
     * secuencial (posición 0, 1, 2, ...) entre los médicos que tienen guardias
     * en un mismo mes, de modo que dentro de un mes no se repita ningún color
     * (mientras haya 15 médicos o menos).
     *
     * El orden está pensado para que médicos consecutivos queden claramente
     * distintos: se intercalan matices (rojo, verde, violeta, mostaza,
     * celeste, fucsia, ...) en lugar de recorrer el círculo cromático de
     * corrido.
     */
    public const PALETA = [
        '#c12525', // rojo
        '#25c152', // verde
        '#8025c1', // violeta
        '#c1ad25', // mostaza
        '#25a7c1', // celeste
        '#c12579', // fucsia
        '#4cc125', // lima
        '#2b25c1', // azul
        '#c15925', // naranja
        '#25c186', // verde agua
        '#b425c1', // magenta
        '#a0c125', // oliva
        '#2573c1', // azul francia
        '#c12545', // carmín
        '#25c132', // verde hoja
    ];

    /**
     * Devuelve el color de la paleta para la posición indicada (0-based). El
     * reparto es secuencial y cíclico: con más de 15 médicos en un mismo mes la
     * paleta vuelve a empezar (y recién ahí se repiten colores).
     */
    public function colorPara(int $orden = 0): string
    {
        $total = count(self::PALETA);
        $indice = $orden % $total;

        if ($indice < 0) {
            $indice += $total;
        }

        return self::PALETA[$indice];
    }

    /**
     * Mapa medico_id => color para un conjunto de médicos que tienen guardias
     * en un mismo mes. La paleta se reparte de forma secuencial (posición 0, 1,
     * 2, ...) siguiendo el orden alfabético de los profesionales, de modo que
     * dentro de un mes nunca se repite un color mientras haya 15 médicos o
     * menos. Entre meses distintos un mismo médico puede cambiar de color.
     *
     * @param  iterable<int|string|null>  $medicoIds
     * @return array<int, string>
     */
    public function colores(iterable $medicoIds): array
    {
        $orden = Profesional::orderBy('nombre')->pluck('id')->flip()->all();

        $ids = collect($medicoIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->sortBy(fn ($id) => $orden[$id] ?? PHP_INT_MAX)
            ->values();

        $colores = [];
        foreach ($ids as $posicion => $id) {
            $colores[$id] = $this->colorPara($posicion);
        }

        return $colores;
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
        if (empty($asignaciones)) {
            return 0;
        }

        // El color se reparte por mes entre todos los médicos que tienen
        // guardias ese mes (los nuevos + los ya guardados), así dentro del mes
        // no se repite ningún color.
        $colorPorMes = [];

        $meses = [];
        foreach (array_keys($asignaciones) as $fecha) {
            $meses[substr($fecha, 0, 7)] = true;
        }

        foreach (array_keys($meses) as $ym) {
            [$anio, $mes] = explode('-', $ym);
            $ids = $this->mes((int) $anio, (int) $mes)
                ->pluck('medico_id')
                ->merge(array_values($asignaciones))
                ->all();

            $colorPorMes[$ym] = $this->colores($ids);
        }

        $total = 0;

        foreach ($asignaciones as $fecha => $medicoId) {
            $medicoId = (int) $medicoId;
            $dia = Carbon::parse($fecha);
            $row = GuardiaMedico::firstOrNew(['fecha' => $fecha]);
            $row->medico_id = $medicoId;
            $row->es_sabado = $dia->isSaturday() ? 1 : 0;
            $row->es_domingo = $dia->isSunday() ? 1 : 0;
            $row->feriado = $feriadoRepository->nombre($fecha);

            $ym = substr($fecha, 0, 7);
            $row->color = $colorPorMes[$ym][$medicoId] ?? $this->colorPara(0);
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