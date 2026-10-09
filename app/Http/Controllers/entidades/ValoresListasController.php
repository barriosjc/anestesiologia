<?php

namespace App\Http\Controllers\entidades;

use App\Http\Controllers\Controller;
use App\Models\Valores;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ValoresListasController extends Controller
{
    public function pdf(Request $request): Response
    {
        $grupo = $request->query('grupo');
        $nivel = $request->query('nivel');

        // Validación mínima: debe existir al menos un dato de filtro.
        if (empty($grupo) && empty($nivel)) {
            abort(422, 'Debe ingresar al menos un filtro (Grupo o Nivel) para generar el PDF.');
        }

        $query = Valores::withTrashed();

        if (!empty($grupo)) {
            $query->where('grupo', $grupo);
        }
        if (!empty($nivel)) {
            $query->where('nivel', $nivel);
        }

        $valores = $query->orderBy('nivel', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $filtros = [
            'grupo' => $grupo,
            'nivel' => $nivel,
        ];

        $pdf = Pdf::loadView('Reportes.Valores.Listado', compact('valores', 'filtros'));

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="valores_listas.pdf"');
    }
}