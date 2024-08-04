<?php

namespace App\Http\Controllers\produccion;

use DateTime;
use Exception;
use App\Models\Centro;
use App\Models\Estado;
use App\Models\Listado;
use App\Models\Periodo;
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
use App\Exports\ProdProfCoberExport;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpParser\Node\Stmt\TryCatch;

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
        $consumos = DB::table('v_consumos')->where("parte_cab_id", $id)->get();
        $soloConsulta = !in_array(Parte_cab::find($id)->estado_id, [3,4]);
        $data = DB::table('v_parte_cab')->find($id);
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
            $parte = Parte_cab::find($request->parte_cab_id);
            $parte->estado_id = 4; //en facturacion
            $parte->save();

            $consumo_cab = Consumo_cab::where('parte_cab_id', $request->parte_cab_id)->first();
            if(empty($consumo_cab)){
                $consumo_cab = new Consumo_cab();
                $consumo_cab->parte_cab_id = $request->parte_cab_id;
                $consumo_cab->user_id = Auth()->user()->id;
                $consumo_cab->save();
            }

            $consumo_det = new Consumo_det;
            $consumo_det->consumo_cab_id = $consumo_cab->id;
            $consumo_det->nomenclador_id = $request->nomenclador_id;
            $consumo_det->porcentaje = $request->porcentaje;
            $consumo_det->cantidad = 1;
            $consumo_det->valor = $request->valor_total;
            $consumo_det->estado_id = 4; //en facturacion
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

    public function rendicion_filtrar(Request $request) 
    {
        $coberturas = Cobertura::get();
        $centros = Centro::get();
        $profesionales = Profesional::get();
        $estados = Estado::get();
        $periodos = Periodo::get();
        $cobertura_id = $request->has('cobertura_id') ? $request->cobertura_id : null;
        $centro_id = $request->has('centro_id') ? $request->centro_id : null;
        $profesional_id = $request->has('profesional_id') ? $request->profesional_id : null;
        $nombre = $request->has('nombre') ? $request->nombre : null;
        $fec_desde = $request->has('fec_desde') ? $request->fec_desde : null;
        $fec_hasta = $request->has('fec_hasta') ? $request->fec_hasta : null;
        $estado_id = $request->has('estado_id') ? $request->estado_id : null;

        $query = DB::table('v_rendiciones');
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
        if(!empty($request->periodo)){
            $query->where('per_nombre', '=', $request->periodo);
        }
        $partes = $query->orderBy('created_at', 'asc')
                    ->paginate();

                    // Ver la consulta SQL y los bindings
// $sql = $query->toSql();
// $bindings = $query->getBindings();

// dd($sql, $bindings);
        return view("consumo.rendiciones", compact("periodos", "partes", "coberturas", "centros", "profesionales", 
                "cobertura_id", "centro_id", "profesional_id", "nombre", "fec_desde", "fec_hasta", "estados", "estado_id"));
    }

    public function rendicion_store(Request $request)
    {
        $this->validate($request, [
            "selected_ids" => "required",
            "periodo" => "required"
        ]);

        $selectedIds = json_decode($request->input('selected_ids'));
        $periodo = $request->input('periodo');
    
        foreach ($selectedIds as $item) {
            $det = Consumo_det::find($item->consumo_det_id);
            $det->periodo = $periodo;
            $det->estado_id = 5; //liquidado
            $det->save();

            $parte = Parte_cab::find($item->parte_id);
            $parte->estado_id = 5;
            $parte->save(); 
        }
    
        // Redirecciona o devuelve una respuesta
        return redirect()->back()->with('success','Se generó la rendición. ');
    }

    public function rendicion_listado(Request $request) 
    {
        $coberturas = Cobertura::get();
        $centros = Centro::get();
        $profesionales = Profesional::get();
        $estados = Estado::get();
        $periodos = Periodo::get();
        $listados = Listado::get();
        
        return view("consumo.listados", compact("periodos", "coberturas", "centros", "profesionales", "estados", "listados"));
        
    }

    public function rendicion_listar(Request $request)
    {
        $this->validate($request, [
            "estados" => "required",
            "reporte_id" => "required",
            "periodo_gen" => "required"
        ]);        

        try {
        if (!$request->reporte_id) {
            throw new Exception('Es obligatorio seleccionar el tipo de reporte a generar.');
        }

        if ($request->reporte_id == 1) {
            if (!($request->profesional_id  && $request->periodo_gen)){
                throw new Exception('Para este tipo de reporte debe seleccionar mínimo Profesional y Periodo');
            }
        }

        $query = DB::table('v_rendicion_agrupxnivel');
        if ($request->has('cobertura_id')  && !empty($request->cobertura_id) ) {
            $query->where('cobertura_id', '=', $request->cobertura_id);
        }
        if ($request->has('centro_id')  && !empty($request->centro_id)) {
            $query->where('centro_id', '=', $request->centro_id);
        }
        if ($request->has('profesional_id')  && !empty($request->profesional_id)) {
            $query->where('profesional_id', '=', $request->profesional_id);
        }
        if ($request->has('nombre')  && !empty($request->nombre)) {
            $query->where('paciente', 'like', "%".$request->nombre."%");
        }
        if ($request->has('periodo_gen')  && !empty($request->periodo_gen)) {
            $query->where('periodo', '=', $request->periodo_gen);
        }
        $selectedEstados = $request->input('estados');
        if (count($selectedEstados) > 1) {
            $query->where(function($query) use ($selectedEstados) {
                foreach ($selectedEstados as $estadoId) {
                    $query->orWhere('estado_id', $estadoId);
                }
            });
        } else {
            $query->where('estado_id', $selectedEstados[0]);
        }
        $consumos = $query->orderBy('parte_cab_id', 'asc')
                    ->get();
        // dd($query->toSql(), $query->getBindings());

        if (count($consumos) == 0) {
            throw new Exception('Atención !!! No se ha encontrado datos para generar la Rendición.accordion.');
        }
        $pdf = Pdf::loadView('Reportes.Rendiciones.ProfFactxCentro', compact("consumos"));
        return $pdf->stream();

        } catch (\Exception $e) {
            return back()->withErrors([$e->getMessage()])
                    ->withInput();
        }
    }
}
