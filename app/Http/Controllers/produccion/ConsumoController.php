<?php

namespace App\Http\Controllers\produccion;

use DateTime;
use Exception;
use App\Models\Centro;
use App\Models\Estado;
use App\Models\Listado;
use App\Models\Periodo;
use App\Models\Valores_cab;
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
use InvalidArgumentException;
// use App\Exports\ProdProfCoberExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\produccion\ReportFactory;
use App\Models\User;
use Carbon\Carbon;

// use PhpParser\Node\Stmt\TryCatch;

class ConsumoController extends Controller
{
    public function parteFiltrar(Request $request)
    {
        $coberturas = Cobertura::orderby("nombre")->get();
        $centros = Centro::orderby("nombre")->get();
        $profesionales = Profesional::get();
        $estados = Estado::get();
        $cobertura_id = $request->has('cobertura_id') ? $request->cobertura_id : session('cobertura_id', null);
        $centro_id = $request->has('centro_id') ? $request->centro_id : session('centro_id', null);
        $profesional_id = $request->has('profesional_id') ? $request->profesional_id : session('profesional_id', null);
        $nombre = $request->has('nombre') ? $request->nombre : session('nombre', null);
        $fec_desde = $request->has('fec_desde') ? $request->fec_desde : session('fec_desde', null);
        $fec_hasta = $request->has('fec_hasta') ? $request->fec_hasta : session('fec_hasta', null);
        $estado_id = $request->has('estado_id') ? $request->estado_id : session('estado_id', null);
        $fec_desde_adm = $request->has('fec_desde_adm') ? $request->fec_desde_adm : session('fec_desde_adm', null);
        $fec_hasta_adm = $request->has('fec_hasta_adm') ? Carbon::parse($request->fec_hasta_adm)->addDay() : session('fec_hasta_adm', null);


        $query = Parte_cab::v_parte_cab();
        if (!empty($cobertura_id)) {
            $query->where('cobertura_id', '=', $cobertura_id);
        }
        if (!empty($centro_id)) {
            $query->where('centro_id', '=', $centro_id);
        }
        if (!empty($profesional_id)) {
            $query->where('profesional_id', '=', $profesional_id);
        }
        if (!empty($estado_id)) {
            $query->where('estado_id', '=', $estado_id);
        }
        if (!empty($nombre)) {
            $query->where('paciente', 'like', "%".$nombre."%");
        }
        if (!empty($fec_desde)) {
            $query->where('fec_prestacion_orig', '>=', $fec_desde);
        }
        if (!empty($fec_hasta)) {
            $query->where('fec_prestacion_orig', '<=', $fec_hasta);
        }
        if (!empty($fec_desde_adm)) {
            $query->where('created_at', '>=', $fec_desde_adm);
        }
        if (!empty($fec_hasta_adm)) {
            $query->where('created_at', '<=', $fec_hasta_adm);
        }
        $partes = $query->orderBy('created_at', 'asc')
                    ->paginate();
    
        // guardo el filtro en session
        session()->put('cobertura_id', $cobertura_id);
        session()->put('centro_id', $centro_id);
        session()->put('profesional_id', $profesional_id);
        session()->put('nombre', $nombre);
        session()->put('fec_desde', $fec_desde);
        session()->put('fec_hasta', $fec_hasta);
        session()->put('fec_desde_adm', $fec_desde_adm);
        session()->put('fec_hasta_adm', $fec_hasta_adm);
        session()->put('estado_id', $estado_id);
// Ver la consulta SQL y los bindings
// $sql = $query->toSql();
// $bindings = $query->getBindings();

// dd($sql, $bindings);
        return view("consumo.partes", compact(
            "partes",
            "coberturas",
            "centros",
            "profesionales",
            "cobertura_id",
            "centro_id",
            "profesional_id",
            "nombre",
            "fec_desde",
            "fec_hasta",
            "estados",
            "estado_id",
            "fec_desde_adm",
            "fec_hasta_adm"
        ));
    }

    public function cargar(int $id)
    {
        $partes_det = Parte_det::where("parte_cab_id", $id)->paginate(3);
        $documentos = Documento::where("tipo", "like", "%parte%")->get();
        $nomenclador = nomenclador::get();
        $periodos = Periodo::orderby("nombre")->get();
        $parte_cab_id = $id;
        $consumos = DB::table('v_consumos')->where("parte_cab_id", $id)->get();
        $soloConsulta = !in_array(Parte_cab::find($id)->estado_id, [3,4]);
        $data = DB::table('v_parte_cab')->find($id);

        $cabecera = $data->sigla ." / ".$data->centro." / ".$data->profesional ." / ".$data->paciente ." (".$data->edad.") / ".$data->fec_prestacion;

        return view("consumo.cargar", compact("periodos", "soloConsulta", "partes_det", "documentos", "parte_cab_id", "nomenclador", "consumos", "cabecera"));
    }
    
    public function valorBuscar(Request $request)
    {
        if (empty($request->periodo)) {
            return response()->json([
                'error' => 'Faltan periodo'
            ], 400); // Devuelve un código de error 400 (Bad Request)
        }
        if (empty($request->parte_cab_id)) {
            return response()->json([
                'error' => 'Faltan id parte'
            ], 400); // Devuelve un código de error 400 (Bad Request)
        }
        if (empty($request->nomenclador_id)) {
            return response()->json([
                'error' => 'Faltan id nomenclador'
            ], 400); // Devuelve un código de error 400 (Bad Request)
        }
        $id = $request->id;
        $parte_cab_id = $request->parte_cab_id;
    
        $id = $request->id;
        $parte_cab_id = $request->parte_cab_id;
        $parte_cab = Parte_cab::where("id", $parte_cab_id)->first();
        $cobertura = cobertura::where("id", $parte_cab->cobertura_id)->first();
        // $grupo = $cobertura->grupo;
        $valores = Valores_cab::v_valores(
            1,
            $parte_cab->cobertura_id,
            $parte_cab->centro_id,
            $request->periodo,
            $request->nomenclador_id
        );
        if (empty($valores)) {
            return response()->json(['valor' => 0, 'porcentaje' => 0]);
        }
        $valor = $valores->valor;
        $porcentaje = 0;

        //calcula edad
        if (!empty($valores->aplica_pocent_adic)) {
            $paciente = Paciente::where("id", $parte_cab->paciente_id)->first();
            $fechaNacimiento = $paciente->fec_nacimiento;
            $fechaNacimiento = new DateTime($fechaNacimiento);
            $fechaActual = new DateTime('today');
            $edad = $fechaActual->diff($fechaNacimiento)->y;
            if (($edad < $cobertura->edad_hasta && $cobertura->edad_hasta != null)  ||
                        ($edad > $cobertura->edad_desde && $cobertura->edad_desde != null)) {
                $porcentaje = $cobertura->porcentaje_adic;
            }
        }
        return response()->json(['valor' => $valor, 'porcentaje' => $porcentaje]);
    }

    public function guardar(Request $request)
    {
        $validate = $request->validate([
            "parte_cab_id" => "required",
            "porcentaje" => "required|numeric|between:1,200",
            "valor_total" => "required|numeric|gt:0",
            "nomenclador_id" => "required"
        ]);

        try {
            $parte = Parte_cab::find($request->parte_cab_id);
            $parte->estado_id = 4; //en facturacion
            $parte->save();

            $consumo_cab = Consumo_cab::where('parte_cab_id', $request->parte_cab_id)->first();
            if (empty($consumo_cab)) {
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

    public function rendicionFiltrar(Request $request)
    {
        $coberturas = Cobertura::orderby("nombre")->get();
        $centros = Centro::orderby("nombre")->get();
        $profesionales = Profesional::get();
        $estados = Estado::get();
        $periodos = Periodo::orderby("nombre")->get();
        $cobertura_id = $request->has('cobertura_id') ? $request->cobertura_id : null;
        $centro_id = $request->has('centro_id') ? $request->centro_id : null;
        $profesional_id = $request->has('profesional_id') ? $request->profesional_id : null;
        $nombre = $request->has('nombre') ? $request->nombre : null;
        $fec_desde = $request->has('fec_desde') ? $request->fec_desde : null;
        $fec_hasta = $request->has('fec_hasta') ? $request->fec_hasta : null;
        $estado_id = $request->has('estado_id') ? $request->estado_id : null;
        $periodo_gen = $request->has('periodo_gen') ? $request->periodo_gen : null;
        

        $query = DB::table('v_rendiciones');
        if ($request->has('cobertura_id')  && !empty($request->cobertura_id)) {
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
        if ($request->has('pac_nombre')  && !empty($request->pac_nombre)) {
            $query->where('pac_nombre', 'like', "%".$request->pac_nombre."%");
        }
        if (!empty($request->fec_desde)) {
            $query->where('fec_prestacion_orig', '>=', $request->fec_desde);
        }
        if (!empty($request->fec_hasta)) {
            $query->where('fec_prestacion_orig', '<=', $request->fec_hasta);
        }
        if (!empty($periodo_gen)) {
            $query->where('periodo', '=', $periodo_gen);
        }
        $partes = $query->orderBy('created_at', 'asc')
                    ->paginate();

                    // Ver la consulta SQL y los bindings
// $sql = $query->toSql();
// $bindings = $query->getBindings();
// dd($sql, $bindings);
        return view("consumo.rendiciones", compact(
            "periodos",
            "partes",
            "coberturas",
            "centros",
            "profesionales",
            "cobertura_id",
            "centro_id",
            "profesional_id",
            "nombre",
            "fec_desde",
            "fec_hasta",
            "estados",
            "estado_id",
            "periodo_gen"
        ));
    }

    public function rendicionStore(Request $request)
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

            $parte = Parte_cab::where("id", $item->parte_id)->first();
            $parte->estado_id = 5;
            $parte->save();
        }
    
        // Redirecciona o devuelve una respuesta
        return redirect()->back()->with('success', 'Se generó la rendición. ');
    }

    public function rendicionListado(Request $request)
    {
        $coberturas = Cobertura::orderby("nombre")->get();
        $centros = Centro::orderby("nombre")->get();
        $profesionales = Profesional::get();
        $estados = Estado::get();
        $periodos = Periodo::orderby("nombre")->get();
        $listados = Listado::get();
        $users = User::get();
        
        return view("consumo.listados", compact("users", "periodos", "coberturas", "centros", "profesionales", "estados", "listados"));
    }

    public function rendicionListar(Request $request)
    {
        try {
            if (!$request->reporte_id) {
                throw new Exception('Es obligatorio seleccionar el tipo de reporte a generar.');
            }
    
            // Crear la estrategia adecuada usando la Factory
            $strategy = ReportFactory::create($request->reporte_id);
          
            // Validar la solicitud usando la estrategia
            $par_adicionales = $strategy->validate($request);

            // Generar el reporte
            $reporte = $strategy->generate($request);
    
            if (count($reporte) == 0) {
                throw new Exception('Atención !!! No se ha encontrado datos para generar la Rendición.');
            }
    
            $parametros = $par_adicionales;
            $parametros["datos"] = $reporte;
            $parametros["parametros"] = $par_adicionales;

            $viewName = $strategy->getViewName();
            $formato = $strategy->getFormat();
            $pdf = Pdf::loadView($viewName, $parametros)
                        ->setPaper($formato->getTamano(), $formato->getOrientacion());
            return $pdf->stream();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors([$e->getMessage()])->withInput();
        }
    }

    public function rendicionEstados(Request $request)
    {
        $this->validate($request, [
            "selected_ids" => "required",
            "estadoCambio" => "required",
            "periodo_refac" => "required_if:estadoCambio,7",
            "obs_refac" => "required_if:estadoCambio,7|max:250"
        ], [
            "selected_ids.required" => "Debe seleccionar de la grilla los procedimientos que requiere cambiar de estado.",
            "periodo_refac.required_if" => "El campo periodo de refacturación es obligatorio cuando se quiere cambiar el estado A refacturar.",
            "obs_refac.required_if" => "El campo Observaciones de refacturación es obligatorio cuando se quiere cambiar el estado A refacturar."
        ]);

        if ($request->has('selected_ids') && $request->has('selected_ids')
                && !empty($request->selected_ids) && !empty($request->estadoCambio)) {
            $selectedIds = $request->input('selected_ids');
            $nuevoEstado = $request->input('estadoCambio');
            $nuevoPeriodo = $request->input('periodo_refac');
            $obs_refac = $request->input('obs_refac');

            try {
                if ($nuevoEstado == "7" && $nuevoPeriodo == null) {
                    throw new InvalidArgumentException("Si el estado es 'A refacturar' es obligatorio ingresar el periodo.");
                }

                $sepa = "";
                $ids = "";
                foreach ($selectedIds as $item) {
                    $consumo = Consumo_det::find($item['consumo_det_id']);
                    $est_actual = $consumo->estado_id;

                    if ($nuevoEstado == "5" && !in_array($est_actual, [6,7,8])) {
                        $ids = $ids . $sepa . $item['parte_id'];
                        $sepa = " ,";
                        continue;
                    }
                    if ($nuevoEstado == "7" && $est_actual != 5) {
                        $ids = $ids . $sepa . $item['parte_id'] ;
                        $sepa = " ,";
                        continue;
                    }
                    if ($nuevoEstado == "8" && $est_actual != 7) {
                        $ids = $ids . $sepa . $item['parte_id'];
                        $sepa = " ,";
                        continue;
                    }
                    if ($nuevoEstado <= "5") {
                        $ids = $ids . $sepa . $item['parte_id'];
                        $sepa = " ,";
                        continue;
                    }
                    if ($nuevoEstado == "6" && in_array($est_actual, [1,2,3,4,6])) {
                        $ids = $ids . $sepa . $item['parte_id'];
                        $sepa = " ,";
                        continue;
                    }

                    $consumo = Consumo_det::where('id', '=', $item['consumo_det_id'])->first();
                    $consumo->estado_id = $nuevoEstado;
                    if ($nuevoEstado == "7") {
                        $consumo->periodo = $nuevoPeriodo;
                        $consumo->obs_refac = $obs_refac;
                    }
                    $consumo->save();
                }

                if (!empty($ids)) {
                    throw new Exception($ids);
                }

                return response()->json(['success' => 'Estado/s actualizado/s con éxito. Vuelva a recargar la página para ver los estados cambiados.'], 200);
            } catch (\InvalidArgumentException $e) {
                return response()->json(['error' => $e->getMessage() . '.'], 422);
            } catch (\Throwable $th) {
                return response()->json(['error'=> 'Algun/os Detalles de rendición no se permite el cambio de estado seleccionado en base a su estado previo a realizar el cambio, nros de parte: '.$th->getMessage().'. '], 422);
            }
        }
    }

    public function rendicionRevalorizar(Request $request)
    {
        $this->validate($request, [
            "selected_ids" => "required",
            "periodo_revalorizar" => "required"
        ]);

        $cantidad = 0;
        $selectedIds = $request->input('selected_ids');
        $periodo = $request->input('periodo_revalorizar');
    
        foreach ($selectedIds as $item) {
            $rendiciones = DB::table('v_rendiciones')
            ->where('consumos_det_id', $item['consumo_det_id'])
            ->first();
           
            $valores = Valores_cab::v_valores(
                1,
                $rendiciones->cobertura_id,
                $rendiciones->centro_id,
                $periodo,
                $rendiciones->nomenclador_id
            );

            if (!empty($valores)) {
                $consumo_det = Consumo_det::find($item['consumo_det_id']);
                $consumo_det->valor = $valores->valor * ($rendiciones->porcentaje / 100);
                $consumo_det->save();
                $cantidad += 1;
            }
        }

        return response()->json(['success' => "Se actualizaron los valores de {$cantidad} consumos."], 200);
    }
}
