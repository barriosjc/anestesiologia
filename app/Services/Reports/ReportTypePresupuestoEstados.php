<?php

namespace App\Services\Reports;

use App\Enums\Orientacion;
use App\Enums\TamanoPapel;
use App\Models\PresupuestoCab;
use Illuminate\Support\Facades\Validator;

class ReportTypePresupuestoEstados implements ReportStrategy
{
    public function validate(array $filtros): array
    {
        $validator = Validator::make($filtros, [
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

    public function generate(array $filtros)
    {
        $datos = PresupuestoCab::with(['profesional', 'centro', 'presupuestosDet.cobertura'])
                    ->whereBetween('fecha', [$filtros['fec_desde'], $filtros['fec_hasta']]);
        $this->applyCommonFilters($datos, $filtros);

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

    private function applyCommonFilters($query, array $filtros)
    {
        if (!empty($filtros['profesional_id'] ?? null)) {
            $query->where('profesional_id', '=', $filtros['profesional_id']);
        }
        if (!empty($filtros['cobertura_id'] ?? null)) {
            $query->whereHas('presupuestosDet', function ($q) use ($filtros) {
                $q->where('cobertura_id', $filtros['cobertura_id']);
            });
        }
        if (!empty($filtros['centro_id'] ?? null)) {
            $query->where('centro_id', '=', $filtros['centro_id']);
        }
        if (!empty($filtros['nombre'] ?? null)) {
            $query->where('paciente', 'like', "%" . $filtros['nombre'] . "%");
        }
        if (!empty($filtros['estado_presupuesto'] ?? null)) {
            $query->where('estado', '=', $filtros['estado_presupuesto']);
        }
    }
}
