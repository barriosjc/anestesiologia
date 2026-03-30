<?php

namespace App\Http\Controllers\produccion;

use App\Enums\Orientacion;
use App\Enums\TamanoPapel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\produccion\ReportStrategy;
use App\Models\PresupuestoCab;
use Illuminate\Support\Facades\Validator;

class ReportTypePresupuestoEstados implements ReportStrategy
{
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            "reporte_id" => "required",
            "fec_desde" => "required",
            "fec_hasta" => "required",
        ], [
            'reporte_id.required' => 'El campo Reporte ID es obligatorio.',
            'fec_desde.required' => 'Es obligatorio seleccionar la Fecha Desde de la cirurgía.',
            'fec_hasta.required' => 'Es obligatorio seleccionar la Fecha Hasta de la cirurgía.',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    public function generate(Request $request)
    {
        $datos = PresupuestoCab::with(['profesional', 'centro', 'presupuestosDet'])
                    ->whereBetween('fecha', [$request->fec_desde, $request->fec_hasta]);
        $this->applyCommonFilters($datos, $request);

        return $datos->get();
    }

    public function getViewName(): string
    {
        return 'Reportes.Presupuestos.Estados';
    }
    
    public function getFormat(): PdfFormat
    {
        return new PdfFormat(TamanoPapel::A4, Orientacion::LANDSCAPE);
    }

    private function applyCommonFilters($query, $request)
    {
        if ($request->has('profesional_id') && !empty($request->profesional_id)) {
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
        // $selectedEstados = $request->input('estados');
        // if (!empty($selectedEstados)) {
        //     if (count($selectedEstados) > 0) {
        //         $query->where(function ($query) use ($selectedEstados) {
        //             foreach ($selectedEstados as $estadoId) {
        //                 $query->orWhere('estado_id', $estadoId);
        //             }
        //         });
        //     } else {
        //         $query->where('estado_id', $selectedEstados[0]);
        //     }
        // }
// $sql = $query->toSql();
// $bindings = $query->getBindings();
// dd("segundo reporte",$sql, $bindings);
    }
}
