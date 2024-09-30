<?php
namespace App\Http\Controllers\produccion;

use App\Enums\Orientacion;
use App\Enums\TamanoPapel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\produccion\ReportStrategy;
use Illuminate\Support\Facades\Validator;

class ReportTypeProduccionAdministrativos implements ReportStrategy
{
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
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

    public function generate(Request $request)
    {
        $query = DB::table('v_parte_cab')
            ->select(DB::raw('name, DATE(created_at) as fecha, COUNT(*) as cantidad'))
            ->whereBetween('created_at', [$request->fec_desde_adm, $request->fec_hasta_adm])
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
    // private function applyCommonFilters($query, $request)
    // {
    //     if ($request->has('cobertura_id') && !empty($request->cobertura_id)) {
    //         $query->where('cobertura_id', '=', $request->cobertura_id);
    //     }
    //     if ($request->has('centro_id') && !empty($request->centro_id)) {
    //         $query->where('centro_id', '=', $request->centro_id);
    //     }
    //     if ($request->has('nombre') && !empty($request->nombre)) {
    //         $query->where('paciente', 'like', "%" . $request->nombre . "%");
    //     }
    //     $selectedEstados = $request->input('estados');
    //     if (count($selectedEstados) > 1) {
    //         $query->where(function ($query) use ($selectedEstados) {
    //             foreach ($selectedEstados as $estadoId) {
    //                 $query->orWhere('estado_id', $estadoId);
    //             }
    //         });
    //     } else {
    //         $query->where('estado_id', $selectedEstados[0]);
    //     }
    // }
}

