<?php

namespace App\Http\Controllers\encuestas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Throwable;
use App\Models\Encuesta;
//use App\Models\Grupal;
//use App\Models\User;
use App\Models\encuesta_resultado;
use App\Models\encuestas_resultados_opciones;
use App\Models\Opcion;
use App\Models\resultado_grupal;
use App\Models\resultado_individual;
use App\Models\empresa;
use App\Models\encuesta_opcion;
use App\Models\Periodo;
use Exception;
use Illuminate\Support\Facades\DB;

class EncuestaController extends Controller
{


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
        $encuestas_opciones = encuesta_opcion::v_encuestas_opciones()->get();
        //DB::connection()->enableQueryLog();
        $encuestas =  $encuesta = Encuesta::v_encuestas()->get();
        //dd( DB::getQueryLog());
        $encuestas_id = null;
        $empresas_id = null;

        return view('encuestas.create_wiz', compact('opciones','empresas','periodos', 'encuestas_opciones', 'encuestas_id', 
                            'empresas_id', 'encuestas'));
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
        return back()->with(['success' => "Se creo o modificó correctamente la cabecera de la encuesta."]);

    }

    public function periodo_store(Request $request)
    {
        $this->validate($request, [
            'h_encuestas_id' => ['required'],
            'descrip_rango' => ['required'],
            'desde' =>['required', 'date'],
            'hasta' => ['required', 'date'],
        ]);

        try {
            if (empty($request->id)) {
                $periodos = new periodo();
            } else {
                $periodos = periodo::where("id", $request->id);
            }
            $periodos->encuestas_id = $request->h_encuestas_id;
            $periodos->descrip_rango = $request->descrip_rango;
            $periodos->desde = $request->desde;
            $periodos->hasta = $request->hasta;
            $periodos->save();

        } catch (Throwable $e) {
           // dd($e->getMessage());
            $msg = $e->getMessage();
            return back()
                    ->withInput($request->input())
                    ->withErrors(['danger' => $msg]);
        }
        return back()->with(['success' => "Se creo o modificó correctamente un periodo de la encuesta."]);

    }

    public function opcion_store(Request $request)
    {
        // dd($request);
        $this->validate($request, [
            'h_encuestas_id' => ['required'],
            'opciones_id' => ['required'],
            'puntos' =>['required', 'integer', "max:9999"],
            'orden' => ['required', 'integer', 'max:99'],
            'style' => ['required', 'max:200'],
        ]);

        try {
            if (empty($request->id)) {
                $encuestas_opciones = new encuesta_opcion();
            } else {
                $encuestas_opciones = encuesta_opcion::where("id", $request->id);
            }
            $encuestas_opciones->encuestas_id = $request->h_encuestas_id;
            $encuestas_opciones->opciones_id = $request->opciones_id;
            $encuestas_opciones->puntos = $request->puntos;
            $encuestas_opciones->orden = $request->orden;
            $encuestas_opciones->style = $request->style;
            $encuestas_opciones->save();

        } catch (Throwable $e) {
           // dd($e->getMessage());
            $msg = $e->getMessage();
            return back()
                    ->withInput($request->input())
                    ->withErrors(['danger' => $msg]);
        }
        return back()->with(['success' => "Se creo o modificó correctamente una opción para la encuesta."]);

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
