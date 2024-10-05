<?php

namespace App\Http\Controllers\produccion;

use App\Enums\Orientacion;
use App\Enums\TamanoPapel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\produccion\ReportStrategy;
use Illuminate\Support\Facades\Validator;

class ReportTypeDetallexProfesional implements ReportStrategy
{

    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            "reporte_id" => "required",
            "profesional_id" => "nullable",
            "fec_desde" => "required|date",
            "fec_hasta" => "required|date"
        ], [
            'reporte_id.required' => 'El campo Reporte ID es obligatorio.',
            'fec_desde.required' => 'El campo Fecha Desde es obligatorio.',
            'fec_desde.date' => 'El campo Fecha Desde debe ser una fecha válida.',
            'fec_hasta.required' => 'El campo Fecha Hasta es obligatorio.',
            'fec_hasta.date' => 'El campo Fecha Hasta debe ser una fecha válida.',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }
        

    public function generate(Request $request)
    {
        $query = DB::table('v_rendiciones');
        $query->whereBetween('fec_prestacion_orig', [$request->fec_desde, $request->fec_hasta]);
        $this->applyCommonFilters($query, $request);
        $resu = $query->get();
//       $sql = $query->toSql();
//  $bindings = $query->getBindings();
// dd($sql, $bindings);
        return $resu;
    }

    public function getViewName(): string
    {
        return 'Reportes.Rendiciones.DetallexProfesional';
    }

    public function getFormat(): PdfFormat
    {
        return new PdfFormat(TamanoPapel::A4, Orientacion::LANDSCAPE);
    }

    private function applyCommonFilters($query, $request)
    {
        if ($request->has("profesional_id") && !empty($request->profesional_id)) {
            $query->where('profesional_id', '=', $request->profesional_id);
        }
        if ($request->has('cobertura_id') && !empty($request->cobertura_id)) {
            $query->where('cobertura_id', '=', $request->cobertura_id);
        }
        if ($request->has('centro_id') && !empty($request->centro_id)) {
            $query->where('centro_id', '=', $request->centro_id);
        }
        if ($request->has('nombre') && !empty($request->nombre)) {
            $query->where('paciente', 'like', "%" . $request->nombre . "%");
        }
        if ($request->has('estados') && !empty($request->estados)) {
            $selectedEstados = $request->input('estados');
            if (count($selectedEstados) > 1) {
                $query->where(function ($query) use ($selectedEstados) {
                    foreach ($selectedEstados as $estadoId) {
                        $query->orWhere('estado_id', $estadoId);
                    }
                });
            }
            // else {
            //     $query->where('estado_id', $selectedEstados[0]);
            // }
        }
    }
}
