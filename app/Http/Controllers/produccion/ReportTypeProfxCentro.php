<?php
namespace App\Http\Controllers\produccion;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\produccion\ReportStrategy;
use Illuminate\Support\Facades\Validator;

class ReportTypeProfxCentro implements ReportStrategy
{
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            "estados" => "required",
            "reporte_id" => "required",
            "profesional_id" => "required",
            "periodo_gen" => "required"
        ], [
            'estados.required' => 'El campo Estados es obligatorio.',
            'reporte_id.required' => 'El campo Reporte ID es obligatorio.',
            'profesional_id.required' => 'El campo Profesional ID es obligatorio.',
            'periodo_get' => 'Es obligatorio seleccionar el periodo de la rendición a generar.'
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();

    }

    public function generate(Request $request)
    {
        $query = DB::table('v_rendicion_agrupxnivel');
        $query->where('profesional_id', '=', $request->profesional_id);
        $query->where('periodo', '=', $request->periodo_gen);
        $this->applyCommonFilters($query, $request);
        return $query->get();
    }

    public function getViewName(): string
    {
        return 'Reportes.Rendiciones.ProfFactxCentro';
    }

    private function applyCommonFilters($query, $request)
    {
        if ($request->has('cobertura_id') && !empty($request->cobertura_id)) {
            $query->where('cobertura_id', '=', $request->cobertura_id);
        }
        if ($request->has('centro_id') && !empty($request->centro_id)) {
            $query->where('centro_id', '=', $request->centro_id);
        }
        if ($request->has('nombre') && !empty($request->nombre)) {
            $query->where('paciente', 'like', "%" . $request->nombre . "%");
        }
        $selectedEstados = $request->input('estados');
        if (count($selectedEstados) > 1) {
            $query->where(function ($query) use ($selectedEstados) {
                foreach ($selectedEstados as $estadoId) {
                    $query->orWhere('estado_id', $estadoId);
                }
            });
        } else {
            $query->where('estado_id', $selectedEstados[0]);
        }
    }
}


