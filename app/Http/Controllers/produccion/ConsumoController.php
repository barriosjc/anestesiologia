<?php

namespace App\Http\Controllers\produccion;

use Exception;
use App\Models\Centro;
use App\Models\Estado;
use App\Models\Valores;
use App\Models\Cobertura;
use App\Models\Documento;
use App\Models\Parte_cab;
use App\Models\Parte_det;
use App\Models\nomenclador;
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
        if ($request->has('profesional_id')  && !empty($request->profesional_id)) {
            $query->where('profesional_id', '=', $request->profesional_id);
        }
        if ($request->has('estado_id')  && !empty($request->estado_id)) {
            $query->where('estado_id', '=', $request->estado_id);
        }
        if ($request->has('nombre')  && !empty($request->nombre)) {
            $query->where('paciente', 'like', "%".$request->nombre."%");
        }
        if(!empty($request->fec_desde)){
            $query->where('fec_prestacion_orig', '>=', $request->fec_desde);
        }
        if(!empty($request->fec_hasta)){
            $query->where('fec_prestacion_orig', '<=', $request->fec_hasta);
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

    public function cargar(int $id)
    {
        $partes_det = Parte_det::where("parte_cab_id", $id)->paginate(3);
        $documentos = Documento::get();
        $nomenclador = nomenclador::get();
        $parte_cab_id = $id;

        return view("consumo.cargar", compact("partes_det", "documentos", "parte_cab_id", "nomenclador" ));
    }

    public function valor_buscar (Request $request)
    {
        $id = $request->id;
        $parte_cab_id = $request->parte_cab_id;
    
        $parte_cab = Parte_cab::where("id", $parte_cab_id)->first();
        $cobertura = cobertura::where("id", $parte_cab->cobertura_id)->first();
        $grupo = $cobertura->grupo; 
        $query = Valores::query();
        $query->where('grupo', '=', $grupo);
        $query->where('nivel', $request->nivel);
        $valor = $query->first()->valor;
        return response()->json(['valor' => $valor]);
    }

    public function guardar (Request $request)
    {
        $validate = $request->validate( [
            "parte_cab_id" => "required",
            "porcentaje" => "required|numeric|between:1,100",
            "valor_total" => "required",
            "nomenclador_id" => "required"
        ]);

        try{

            return redirect()->back();

        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

    }
}
