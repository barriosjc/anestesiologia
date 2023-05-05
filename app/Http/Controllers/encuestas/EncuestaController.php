<?php

namespace App\Http\Controllers\encuestas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Throwable;
use App\Models\Encuesta;
use App\Models\Grupal;
use App\Models\User;
use App\Models\encuesta_resultado;
use App\Models\encuestas_resultados_opciones;
use App\Models\Opcion;
use App\Models\resultado_grupal;
use App\Models\resultado_individual;
use App\Models\empresa;
use App\Models\encuesta_opcion;
use App\Models\Periodo;
use Exception;

class EncuestaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $titulo = "Sin encuesta para este periodo";
        $encuesta = Encuesta::v_encuesta_actual()->get();
        //traer todos los usuarios de la empresa y excluye al users_id
        $users = User::all();
        //traer todos los grupos de la empresa del usuario
        $grupal = Grupal::all();
        if(count($encuesta)) $titulo = $encuesta[0]->edicion . " - ". $encuesta[0]->descrip_rango;

        return view('encuestas.encuesta', compact('encuesta', 'users', 'grupal', 'titulo'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $titulo = "Alta de una encuesta";
        $opciones = Opcion::all();
        $empresas = empresa::all();
        $periodos = Periodo::all();
        $encuestas_opciones = encuesta_opcion::all();
        $encuestas =  $encuesta = Encuesta::v_encuestas()->get();
        $encuestas_id = null;
        $empresas_id = null;

        return view('encuestas.create', compact('opciones','empresas','periodos', 'encuestas_opciones', 'encuestas_id', 
                            'empresas_id', 'encuestas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $valant= [];
        $this->validate($request, [
            'observaciones' => 'max:200',
        ]);

        try {

            if (!isset($request->opciones)) {
                throw new Exception("Debe completar el Paso 1, es obligatortio seleccionar por lo menos un 'motivo', tilde la opcion que desee.");
            }
            if (!isset($request->ck_tipo)) {
                throw new Exception("Debe completar el Paso 2, seleccionar la opcion y el item de la lista");
            }
            if ($request->ck_tipo == "ck_individual" && !isset($request->user_id_reconocido)) {
                throw new Exception("Si selecciona valorar a un compañero, debe seleccionar a un compañero de la lista. (Paso 2)");
            }
            if ($request->ck_tipo == "ck_grupal" && !isset($request->grupal_id_reconocido)) {
                throw new Exception("Si selecciona valorar a un grupo o sector, debe seleccionar a un grupo de la lista. (Paso 2)");
            }

            $campos = "observaciones, adjunto, users_id, encuestas_id";
            $data = $this->conDatos($request, $campos);
            //  dd($data);
            // if(!isset($request->observaicones)){
            //     $valant['observaciones'] = $request->observaicones;
            // }

            $encuesta_result = new encuesta_resultado();
            $encuesta_result->encuestas_id = $data['encuestas_id'];
            $encuesta_result->users_id = $data['users_id'];
            $encuesta_result->observaciones = $data['observaciones'];
            $encuesta_result->save();

            foreach ($request->opciones as $opcion) {
                $enc_res_opciones = new encuestas_resultados_opciones();
                $enc_res_opciones->opciones_id = $opcion;
                $enc_res_opciones->encuestas_resultados_id = $encuesta_result->id;
                $enc_res_opciones->save();

                if ($request->ck_tipo == 'ck_individual') {
                    $resultado = new Resultado_individual();
                    $resultado->encuestas_resultados_opciones_id = $enc_res_opciones->id;
                    $resultado->users_id = $request->user_id_reconocido;
                    $resultado->puntos = $request->puntos[$opcion];
                    $resultado->save();
                }else{
                    $resultado = new Resultado_grupal();
                    $resultado->encuestas_resultados_opciones_id = $enc_res_opciones->id;
                    $resultado->grupal_id = $request->user_id_reconocido;
                    $resultado->puntos = $request->puntos[$opcion];
                    $resultado->save();
                }
            }
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            //return back()->with(['danger' => $msg, "valant" => $valant]);
            return back()
                    ->withInput($request->input())
                    ->withErrors(['danger' => $msg]);
        }

        return redirect()->route('respuesta')
            ->with('success', 'Se guardó la en encuesta en forma correcta.');
    }

    public function create_store(Request $request){
        $this->validate($request, [
            'empresas_id' => ['required'],
            'opcionesxcol' => ['required', 'in:[2,3,4,5]'],
            'encuesta' =>['required', 'max:50'],
            'edicion' => ['required', 'max:200'],
        ]);
        try {
            if (empty($request->id)) {
                $encuesta = new Encuesta();
            } else {
                $encuesta = Encuesta::where("id", $request->id);
            }
            $encuesta->empresas_id = $request->empresas_id;
            $encuesta->encuesta = $request->encuesta;
            $encuesta->edicion = $request->edicion;
            $encuesta->opcionesxcol = $request->opcionesxcol;
            $encuesta->habilitada = isset($request->habilitada) ? 1 : 0;
            $encuesta->save();

        } catch (Throwable $e) {
           // dd($e->getMessage());
            $msg = $e->getMessage();
            return back()
                    ->withInput($request->input())
                    ->withErrors(['danger' => $msg]);
        }
// dd("va po el back");
        return back()->withInput($request->input())
                     ->with(['success' => "Se creo correctamente la cabecera de la encuesta."]);

    }

    private function conDatos($request, $campos)
    {
        $array = explode(",", $campos);
        $resu = [];
        foreach ($array as $campo) {
            $campo = ltrim($campo);
            if (isset($request[$campo])) {
                $resu[$campo] = $request->input($campo);
            }
        }

        return $resu;
    }
}
