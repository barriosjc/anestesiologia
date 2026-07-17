<?php

namespace App\Services\Reports;

use App\Enums\Orientacion;
use App\Enums\TamanoPapel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportTypeProduccionAdministrativos implements ReportStrategy
{
    public function validate(array $filtros): array
    {
        $validator = Validator::make($filtros, [
            "fec_desde_adm" => "required",
            "fec_hasta_adm" => "required",
            "user_id" => "nullable",
        ], [
            'fec_desde_adm.required' => 'El campo Fecha desde parte es obligatorio.',
            'fec_hasta_adm.required' => 'El campo Fecha hasta parte es obligatorio.'
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    public function generate(array $filtros)
    {
        $query = DB::table('v_parte_cab')
            ->select(DB::raw('name, DATE(created_at) as fecha, COUNT(*) as cantidad'))
            ->whereBetween('created_at', [$filtros['fec_desde_adm'], $filtros['fec_hasta_adm']])
            ->groupBy('name', DB::raw('DATE(created_at)'))
            ->get();

        return $query;
    }

    public function getViewName(): string
    {
        return 'Reportes.Partes.Produccion';
    }

    public function getFormat(): PdfFormat
    {
        return new PdfFormat(TamanoPapel::A4, Orientacion::PORTRAIT);
    }
}
