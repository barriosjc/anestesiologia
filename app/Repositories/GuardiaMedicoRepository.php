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
     * Color bien diferenciado según la posición del médico en la lista
     * ordenada. Usa el ángulo áureo (137.5°) sobre el matiz HSL para que
     * médicos consecutivos siempre tengan colores claramente distintos.
     */
    public function colorPara(int $medicoId, int $orden = 0): string
    {
        $hue = fmod($orden * 137.508, 360);
        if ($hue < 0) {
            $hue += 360;
        }

        return $this->hslToHex($hue, 0.72, 0.45);
    }

    private function hslToHex(float $hue, float $saturation, float $lightness): string
    {
        $c = (1 - abs(2 * $lightness - 1)) * $saturation;
        $x = $c * (1 - abs(fmod($hue / 60, 2) - 1));
        $m = $lightness - $c / 2;

        $rgb = match ((int) floor($hue / 60)) {
            0 => [$c, $x, 0],
            1 => [$x, $c, 0],
            2 => [0, $c, $x],
            3 => [0, $x, $c],
            4 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return sprintf(
            '#%02x%02x%02x',
            (int) round(($rgb[0] + $m) * 255),
            (int) round(($rgb[1] + $m) * 255),
            (int) round(($rgb[2] + $m) * 255),
        );
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

        $indiceId = $this->medicos()->pluck('id')->flip()->all();

        foreach ($asignaciones as $fecha => $medicoId) {
            $medicoId = (int) $medicoId;
            $dia = Carbon::parse($fecha);
            $row = GuardiaMedico::firstOrNew(['fecha' => $fecha]);
            $row->medico_id = $medicoId;
            $row->es_sabado = $dia->isSaturday() ? 1 : 0;
            $row->es_domingo = $dia->isSunday() ? 1 : 0;
            $row->feriado = $feriadoRepository->nombre($fecha);

            $colorPrevio = GuardiaMedico::where('medico_id', $medicoId)
                ->whereNotNull('color')
                ->latest('fecha')
                ->value('color');
            $row->color = $colorPrevio ?? $this->colorPara($medicoId, $indiceId[$medicoId] ?? $medicoId);
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