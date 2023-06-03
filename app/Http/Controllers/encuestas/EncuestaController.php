<?php

namespace App\Http\Controllers\encuestas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Throwable;
use App\Models\Encuesta;
use App\Models\Opcion;
use App\Models\Empresa;
use App\Models\encuesta_opcion;
use App\Models\Periodo;
use Exception;
use Illuminate\Support\Facades\DB;

class EncuestaController extends Controller
{

    protected $listeners = ['borrar_encuesta' => 'borrar_encuesta'];

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


}
