<?php

namespace App\Services\Reports;

use App\Enums\Orientacion;
use App\Enums\TamanoPapel;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportTypeProfxCentro implements ReportStrategy
{
    public function validate(array $filtros): array
    {
        $validator = Validator::make($filtros, [
            "reporte_id" => "required",
            "periodo_gen" => "required"
        ], [
            'reporte_id.required' => 'El campo Reporte ID es obligatorio.',
            'periodo_gen' => 'Es obligatorio seleccionar el periodo de la rendición a generar.'
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    public function generate(array $filtros): Collection
    {
        $query = DB::table('v_rendicion_agrupxnivel');
        $query->where('periodo', '=', $filtros['periodo_gen']);
        $this->applyCommonFilters($query, $filtros);
        return $query->get();
    }

    public function getViewName(): string
    {
        return 'Reportes.Rendiciones.ProfFactxCentro';
    }

    public function getFormat(): PdfFormat
    {
        return new PdfFormat(TamanoPapel::A4, Orientacion::LANDSCAPE);
    }

    private function applyCommonFilters(Builder $query, array $filtros): void
    {
        if (!empty($filtros['profesional_id'] ?? null)) {
            $query->where('profesional_id', '=', $filtros['profesional_id']);
        }
        if (!empty($filtros['cobertura_id'] ?? null)) {
            $query->where('cobertura_id', '=', $filtros['cobertura_id']);
        }
        if (!empty($filtros['centro_id'] ?? null)) {
            $query->where('centro_id', '=', $filtros['centro_id']);
        }
        if (!empty($filtros['nombre'] ?? null)) {
            $query->where('paciente', 'like', "%" . $filtros['nombre'] . "%");
        }
        $selectedEstados = $filtros['estados'] ?? null;
        if (!empty($selectedEstados)) {
            if (count($selectedEstados) > 0) {
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
}
