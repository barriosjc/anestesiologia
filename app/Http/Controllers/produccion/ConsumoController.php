<?php

namespace App\Http\Controllers\produccion;

use App\Models\Centro;
use App\Models\Estado;
use App\Models\Cobertura;
use App\Models\Parte_cab;
use App\Models\Profesional;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConsumoController extends Controller
{
    public function parte_filtrar(Request $request) 
    {
        $coberturas = Cobertura::get();
        $centros = Centro::get();
        $profesionales = Profesional::get();
        $estados = Estado::get();
        $cobertura_id = $request->has('cobertura_id') ? $request->cobertura_id : null;
        $centro_id = $request->has('centro_id') ? $request->centro_id : null;
        $profesional_id = $request->has('profesional_id') ? $request->profesional_id : null;
        $nombre = $request->has('nombre') ? $request->nombre : null;
        $fec_desde = $request->has('fec_desde') ? $request->fec_desde : null;
        $fec_hasta = $request->has('fec_hasta') ? $request->fec_hasta : null;
        $estado_id = $request->has('estado_id') ? $request->estado_id : null;

        $query = Parte_cab::v_parte_cab();
        if ($request->has('cobertura_id')  && !empty($request->cobertura_id) ) {
            $query->where('cobertura_id', '=', $request->cobertura_id);
        }
        if ($request->has('centro_id')  && !empty($request->centro_id)) {
            $query->where('centro_id', '=', $request->centro_id);
        }
        // dd(empty($request->fec_desde), $request->fec_desde);
        if(!empty($request->fec_desde)){
            $query->where('fec_prestacion_orig', '>=', $request->fec_desde);
        }
        $partes = $query->orderBy('created_at', 'asc')
                    ->paginate();

                    // Ver la consulta SQL y los bindings
// $sql = $query->toSql();
// $bindings = $query->getBindings();

// dd($sql, $bindings);
        return view("consumo.partes", compact("partes", "coberturas", "centros", "profesionales", 
                "cobertura_id", "centro_id", "profesional_id", "nombre", "fec_desde", "fec_hasta", "estados", "estado_id"));
    }
}
