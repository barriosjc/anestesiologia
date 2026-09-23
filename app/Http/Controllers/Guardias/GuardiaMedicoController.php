<?php

namespace App\Http\Controllers\Guardias;

use App\Http\Controllers\Controller;
use App\Repositories\GuardiaMedicoRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class GuardiaMedicoController extends Controller
{
    public function pdf(Request $request, GuardiaMedicoRepository $guardiaRepo): Response
    {
        $anio = (int) $request->query('anio', now()->year);
        $mes = (int) $request->query('mes', now()->month);
        $incluir = (bool) $request->query('incluir', false);

        $primerDia = Carbon::create($anio, $mes, 1);
        $titulo = 'Guardias de ' . ucfirst($primerDia->translatedFormat('F Y'));

        $tomadas = $guardiaRepo->mes($anio, $mes)->values();

        $resumen = $tomadas
            ->groupBy('medico_id')
            ->map(function ($guardias, $medicoId) {
                $medico = $guardias->first()->medico;

                return [
                    'medico' => $medico?->nombre ?? '(sin asignar)',
                    'habiles' => $guardias
                        ->filter(fn ($g) => !$g->es_sabado && !$g->es_domingo && empty($g->feriado))
                        ->count(),
                    'feriados_findes' => $guardias
                        ->filter(fn ($g) => $g->es_sabado || $g->es_domingo || !empty($g->feriado))
                        ->count(),
                ];
            })
            ->sortBy('medico')
            ->values();

        $habiles = $resumen->filter(fn ($fila) => $fila['habiles'] > 0)->values();
        $feriadosFindes = $resumen->filter(fn ($fila) => $fila['feriados_findes'] > 0)->values();

        $pdf = Pdf::loadView('reportes.Guardias.Informe', compact('titulo', 'habiles', 'feriadosFindes', 'incluir'));

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="guardias_' . $anio . '_' . $mes . '.pdf"');
    }
}