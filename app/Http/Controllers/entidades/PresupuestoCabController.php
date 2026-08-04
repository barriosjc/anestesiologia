<?php

namespace App\Http\Controllers\entidades;

use App\Http\Controllers\Controller;
use App\Models\PresupuestoCab;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PresupuestoCabController extends Controller
{
    public function print(int $id, \App\Repositories\PresupuestoDetalleRepository $presupuestoDetalleRepository): Response
    {
        $presupuesto = PresupuestoCab::with(['pagos', 'centro', 'user'])->findOrFail($id);
        $presupuesto->presupuestosDet = $presupuestoDetalleRepository->detalleConDescripcion($id);

        $pdf = Pdf::loadView('reportes.Presupuestos.Informe', compact('presupuesto'));

        //return $pdf->stream('presupuesto_'.$presupuesto->id.'.pdf');
        // Si querés que se descargue automáticamente:
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="presupuesto_' . $presupuesto->id . '.pdf"');
    }
}
