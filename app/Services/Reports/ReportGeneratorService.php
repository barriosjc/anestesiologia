<?php

namespace App\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class ReportGeneratorService
{
    public function validar(?int $reporteId, array $filtros): array
    {
        if (empty($reporteId)) {
            throw new Exception('Es obligatorio seleccionar el tipo de reporte a generar.');
        }

        $strategy = ReportFactory::create($reporteId);

        return $strategy->validate($filtros);
    }

    public function generar(?int $reporteId, array $filtros)
    {
        if (empty($reporteId)) {
            throw new Exception('Es obligatorio seleccionar el tipo de reporte a generar.');
        }

        $strategy = ReportFactory::create($reporteId);
        $parAdicionales = $strategy->validate($filtros);

        $reporte = $strategy->generate($filtros);

        if (count($reporte) == 0) {
            throw new Exception('Atención !!! No se ha encontrado datos para generar la Rendición.');
        }

        if ($reporteId == 2) {
            $conObs = $reporte->contains(function ($item) {
                return in_array($item->estado_id, [7, 8]);
            });
            $parAdicionales["conObs"] = $conObs;
        }

        $parametros = $parAdicionales;
        $parametros["datos"] = $reporte;
        $parametros["parametros"] = $parAdicionales;

        $formato = $strategy->getFormat();

        return Pdf::loadView($strategy->getViewName(), $parametros)
            ->setPaper($formato->getTamano(), $formato->getOrientacion());
    }
}
