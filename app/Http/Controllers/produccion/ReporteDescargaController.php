<?php

namespace App\Http\Controllers\produccion;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportGeneratorService;
use Illuminate\Http\Request;

class ReporteDescargaController extends Controller
{
    public function stream(Request $request, ReportGeneratorService $reportGeneratorService)
    {
        $reporteId = $request->query('reporte_id') ? (int) $request->query('reporte_id') : null;

        try {
            $pdf = $reportGeneratorService->generar($reporteId, $request->query());

            return $pdf->stream();
        } catch (\Exception $e) {
            return response()->view('Reportes.error', ['mensaje' => $e->getMessage()], 422);
        }
    }
}
