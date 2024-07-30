<?php

namespace App\Http\Controllers\produccion;

use DateTime;
use Exception;
use App\Models\Centro;
use App\Models\Estado;
use App\Models\Valores;
use App\Models\Paciente;
use App\Models\Cobertura;
use App\Models\Documento;
use App\Models\Parte_cab;
use App\Models\Parte_det;
use App\Models\Consumo_cab;
use App\Models\Consumo_det;
use App\Models\nomenclador;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $consumos = DB::table('v_consumos')->get();
        $data = DB::table('v_parte_cab')->find($id);
        $soloConsulta = in_array(Parte_cab::find($id)->estado_id, [3,1]);
        $cabecera = $data->cobertura ." / ".$data->centro." / ".$data->profesional ." / ".$data->paciente ." (".$data->fec_nacimiento.") / ".$data->fec_prestacion;

        return view("consumo.cargar", compact("soloConsulta", "partes_det", "documentos", "parte_cab_id", "nomenclador", "consumos", "cabecera" ));
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
        $nom_valor = $query->first();
        $valor = $nom_valor->valor;
        $porcentaje = 0;

        //calcula edad
        if(!empty($nom_valor->aplica_pocent_adic))
        {
            $paciente = Paciente::where("id", $parte_cab->paciente_id)->first();  
            $fechaNacimiento = $paciente->fec_nacimiento;
            $fechaNacimiento = new DateTime($fechaNacimiento);
            $fechaActual = new DateTime('today');
            $edad = $fechaActual->diff($fechaNacimiento)->y;
            if(($edad < $cobertura->edad_hasta && $cobertura->edad_hasta != null)  || 
                        ($edad > $cobertura->edad_desde && $cobertura->edad_desde != null)){
                $porcentaje = $cobertura->porcentaje_adic;
            }
        }
        return response()->json(['valor' => $valor, 'porcentaje' => $porcentaje]);
    }

    public function guardar (Request $request)
    {
        $validate = $request->validate( [
            "parte_cab_id" => "required",
            "porcentaje" => "required|numeric|between:1,200",
            "valor_total" => "required|numeric|gt:0",
            "nomenclador_id" => "required"
        ]);

        try{
            $consumo_cab = Consumo_cab::where('parte_cab_id', $request->parte_cab_id)->first();
            if(empty($consumo_cab)){
                $consumo_cab = new Consumo_cab();
                $consumo_cab->parte_cab_id = $request->parte_cab_id;
                $consumo_cab->estado_id = 1;
                $consumo_cab->user_id = Auth()->user()->id;
                $consumo_cab->save();
            }

            $consumo_det = new Consumo_det;
            $consumo_det->consumo_cab_id = $consumo_cab->id;
            $consumo_det->nomenclador_id = $request->nomenclador_id;
            $consumo_det->porcentaje = $request->porcentaje;
            $consumo_det->cantidad = 1;
            $consumo_det->valor = $request->valor_total;
            $consumo_det->save();

            return redirect()->route('consumos.cargar', $request->parte_cab_id);

        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, $id) 
    {
        $profesional = Consumo_det::find($id)->delete();

        return redirect()->back();
    }

    public function observar(Request $request) 
    {       
        $validate = $request->validate([
            "id" => "required",
            "observaciones" => "required|max:255"
        ]);
        
        $result = $this->cambiaEstado($validate, 2);
    
        if ($result['success']) {
            return redirect()->route("consumos.partes.filtrar")->with('success', 'Estado cambiado exitosamente.');
        } else {
            return redirect()->back()->withErrors($result['message'])->withInput();
        }
    }
    
    public function aProcesar(Request $request) 
    {       
        $validate = $request->validate([
            "id" => "required",
            "observaciones" => "nullable|max:255"
        ]);
        
        $result = $this->cambiaEstado($validate, 3);
    
        if ($result['success']) {
            return redirect()->route("partes_cab.index")->with('success', 'Estado cambiado exitosamente.');
        } else {
            return redirect()->back()->withErrors($result['message'])->withInput();
        }
    }
    
    private function cambiaEstado($request, $estado_id)
    {
        try {
            $parte = parte_cab::find($request["id"]);
            if (!($parte->estado_id == 1 || $parte->estado_id == 2) && $estado_id == 3) {
                throw new \Exception('El estado no puede ser cambiado a "A liquidar" desde el estado actual.');
            }
            if (!($parte->estado_id == 1 || $parte->estado_id == 3) && $estado_id == 2) {
                throw new \Exception('El estado no puede ser cambiado a "Observado" desde el estado actual.');
            }
            $parte->observaciones = $request["observaciones"];
            $parte->estado_id = $estado_id; 
            $parte->save();
    
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }    
    
}
