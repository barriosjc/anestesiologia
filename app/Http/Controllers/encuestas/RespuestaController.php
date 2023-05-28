<?php

namespace App\Http\Controllers\encuestas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Throwable;
use Exception;

use App\Models\Encuesta;
use App\Models\encuesta_resultado;
use App\Models\encuestas_resultados_opciones;
//use App\Models\Opcion;
use App\Models\resultado_grupal;
use App\Models\resultado_individual;
//use App\Models\empresa;
//use App\Models\encuesta_opcion;
//use App\Models\Periodo;
use App\Models\User;
//use App\Models\Grupal;
use Illuminate\Support\Facades\DB;


class RespuestaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth()->User()->empresas_id < 1) {
            abort(403, "No posee acceso para ingresar a Respuesta de encuestas, debe tener una empresa asignada");
        }
        //$empresas_id = $request->input('empresas_id');
        // $grupal = grupal::v_grupal(Auth()->User()->empresas_id)->get();
        // $response = ['data' => $grupal];
        // dd($response);

        $titulo = "Sin encuesta para este periodo";
        $encuestas = Encuesta::v_encuesta_actual(Auth()->user()->empresas_id)
            ->get();
        //traer todos los usuarios de la empresa y excluye al users_id
        $users = User::where("empresas_id", Auth()->user()->empresas_id)
                        ->where("id", "!=", Auth()->user()->id)
                        ->get();
        //traer todos los grupos de la empresa del usuario
        $grupal = DB::select('select id, useryjefe from v_user_jefes where empresas_id = ' . Auth()->user()->empresas_id
                                   . " and id != " . Auth()->user()->id);
        if (count($encuestas)) $titulo = $encuestas[0]->razon_social  . " - " . $encuestas[0]->edicion . " - " . $encuestas[0]->descrip_rango;

        return view('encuestas.respuesta', compact('encuestas', 'users', 'grupal', 'titulo'));
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

            //esto luego tiene que estar dentro de un bucle por el select multi
            if ($request->ck_tipo == 'ck_individual') {
                $resultado = new Resultado_individual();
                $resultado->encuestas_resultados_id = $encuesta_result->id;
                $resultado->users_id = $request->user_id_reconocido;
                $resultado->save();
            } else {
                foreach ($request->grupal_id_reconocido as $key => $value) {
                    $resultado = new Resultado_grupal();
                    $resultado->encuestas_resultados_id = $encuesta_result->id;
                    $resultado->user_id = $value;
                    $resultado->save();
                }
            }

            foreach ($request->opciones as $opcion) {
                $enc_res_opciones = new encuestas_resultados_opciones();
                $enc_res_opciones->opciones_id = $opcion;
                $enc_res_opciones->puntos = $request->puntos[$opcion];
                $enc_res_opciones->encuestas_resultados_id = $encuesta_result->id;
                $enc_res_opciones->save();
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
