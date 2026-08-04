<?php

namespace App\Services\Reports;

use App\Enums\Orientacion;
use App\Enums\TamanoPapel;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportTypeDetallexProfesional implements ReportStrategy
{
    public function validate(array $filtros): array
    {
        $validator = Validator::make($filtros, [
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

    public function generate(array $filtros): Collection
    {
        $query = DB::table('v_rendiciones');
        $query->whereBetween('fec_prestacion_orig', [$filtros['fec_desde'], $filtros['fec_hasta']]);
        $this->applyCommonFilters($query, $filtros);
        $resu = $query->get();

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
            $query->where('pac_nombre', 'like', "%" . $filtros['nombre'] . "%");
        }

        if (!empty($filtros['estados'] ?? null)) {
            $selectedEstados = $filtros['estados'];
            if (count($selectedEstados) > 0) {
                $query->where(function ($query) use ($selectedEstados) {
                    foreach ($selectedEstados as $estadoId) {
                        $query->orWhere('estado_id', $estadoId);
                    }
                });
            }
        }
    }
}
