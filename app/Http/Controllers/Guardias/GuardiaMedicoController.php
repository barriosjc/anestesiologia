<?php

namespace App\Http\Controllers\Guardias;

use App\Http\Controllers\Controller;
use App\Models\GuardiaMedico;
use App\Repositories\FeriadoRepository;
use App\Repositories\GuardiaMedicoRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class GuardiaMedicoController extends Controller
{
    public function pdf(Request $request, GuardiaMedicoRepository $guardiaRepo, FeriadoRepository $feriadoRepo): Response
    {
        $anio = (int) $request->query('anio', now()->year);
        $mes = (int) $request->query('mes', now()->month);
        $incluir = filter_var($request->query('incluir', false), FILTER_VALIDATE_BOOLEAN);

        $primerDia = Carbon::create($anio, $mes, 1);
        $titulo = 'Guardias de ' . ucfirst($primerDia->translatedFormat('F Y'));

        $tomadas = $guardiaRepo->mes($anio, $mes)->values();

        $feriados = array_flip($feriadoRepo->fechasDelMes($anio, $mes));

        $esFinDeSemanaOFeriado = fn (GuardiaMedico $g) => $g->fecha->isSaturday()
            || $g->fecha->isSunday()
            || isset($feriados[$g->fecha->format('Y-m-d')]);

        $resumen = $tomadas
            ->groupBy('medico_id')
            ->map(function ($guardias) use ($esFinDeSemanaOFeriado) {
                $medico = $guardias->first()->medico;

                return [
                    'medico' => $medico?->nombre ?? '(sin asignar)',
                    'habiles' => $guardias->reject($esFinDeSemanaOFeriado)->count(),
                    'feriados_findes' => $guardias->filter($esFinDeSemanaOFeriado)->count(),
                ];
            })
            ->sortBy('medico')
            ->values();

        $habiles = $resumen->filter(fn ($fila) => $fila['habiles'] > 0)->values();
        $feriadosFindes = $resumen->filter(fn ($fila) => $fila['feriados_findes'] > 0)->values();

        $pdf = Pdf::loadView('Reportes.Guardias.Informe', compact('titulo', 'habiles', 'feriadosFindes', 'incluir'));

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="guardias_' . $anio . '_' . $mes . '.pdf"');
    }
}