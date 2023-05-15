<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

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
use Illuminate\Support\Facades\DB;

class CreateEncuesta extends Component
{
    use WithPagination;
protected $paginationTheme = 'bootstrap';

    public $currentTab = 1;
    public $encuestas_id = null;
    //public $empresas =[];
    //public $periodos = [];
    //public $opciones = [];
    //public $encuestas_opciones = [];

    //encuesta
    public $encuestas_id_modif = null;
    public $encuestas_id_selected = null;
    public $e_empresas_id = null;
    public $e_encuesta = null;
    public $e_edicion = null;
    public $e_opcionesxcol = null;
    public $e_habilitada = null;
    //periodos
    public $periodos_id_modif = null;
    public $p_encuestas_id = null;
    public $p_descrip_rango = null;
    public $p_desde = null;
    public $p_hasta = null;
    //opciones
    public $o_opciones_id = null;
    public $o_puntos = null;
    public $o_orden = null;
    public $o_style = null;

    public function render()
    {
        $titulo = "Alta de una encuesta";
        $cant_per = null;
        $cant_opc = null;
        if (Auth()->user()->empresas_id === 0) {
        $empresas = empresa::all();
        $encuestas =  $encuesta = Encuesta::v_encuestas()
                        ->paginate(5, ['*'], '_encuestas');
        $periodos = Periodo::where('encuestas_id', $this->encuestas_id_selected)
                        ->paginate(5, ['*'], '_periodos');
        $cant_per = Periodo::where('encuestas_id', $this->encuestas_id_selected)
                        ->count();
        $encuestas_opciones = encuesta_opcion::v_encuestas_opciones()
                        ->where('encuestas_id', $this->encuestas_id_selected)
                        ->paginate(5, ['*'], '_opciones');
        $cant_opc =  encuesta_opcion::v_encuestas_opciones()
                        ->where('encuestas_id', $this->encuestas_id_selected)
                        ->count();
        $opciones = Opcion::all();
        } else {
            $empresas = empresa::where("id", Auth()->user()->empresas_id)
                            ->get();
            $encuestas =  $encuesta = Encuesta::v_encuestas()
                            ->where("empresas_id", Auth()->user()->empresas_id)
                            ->paginate(5, ['*'], '_encuestas');
            $periodos = Periodo::
                            where('encuestas_id', $this->encuestas_id_selected)
                            ->paginate(5, ['*'], '_periodos');
            $encuestas_opciones = encuesta_opcion::v_encuestas_opciones()
                            ->where('encuestas_id', $this->encuestas_id_selected)
                            ->paginate(5, ['*'], '_opciones');
            $opciones = Opcion::all();
        }

        //DB::connection()->enableQueryLog();
        //dd( DB::getQueryLog());
        // $encuestas_id = null;
        // $empresas_id = null;

        return view('livewire.create-encuesta')->with(compact('titulo', 'empresas', 'encuestas', 'periodos', 
                                'encuestas_opciones', 'opciones', 'cant_per', 'cant_opc'));
    }

    public function itemEncuestas_id($id)
    {
        $this->encuestas_id_selected = $id;

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $titulo = "Alta de una encuesta";
        $opciones = Opcion::all();
        $empresas = empresa::all();
        $periodos = Periodo::all();
        $encuestas_opciones = encuesta_opcion::v_encuestas_opciones()->get();
        //DB::connection()->enableQueryLog();
        $this->encuestas = Encuesta::v_encuestas()->get();
        //dd( DB::getQueryLog());
        $encuestas_id = null;
        $empresas_id = null;

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
        $encuestas_opciones = encuesta_opcion::v_encuestas_opciones()->get();
        //DB::connection()->enableQueryLog();
        $encuestas =  $encuesta = Encuesta::v_encuestas()->get();
        //dd( DB::getQueryLog());
        $encuestas_id = null;
        $empresas_id = null;

        return view('encuestas.create_wiz', compact(
            'opciones',
            'empresas',
            'periodos',
            'encuestas_opciones',
            'encuestas_id',
            'empresas_id',
            'encuestas'
        ));
    }

    /**
     * guarda el resultado de un encuentas, las respuestas a los cuadros seleccionados
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
                } else {
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

    public function encuesta_store()
    {

        try {
            if (empty($this->encuestas_id_modif)) {
                $encuesta = new Encuesta();
            } else {
                $encuesta = Encuesta::where("id", $this->encuestas_id_modif);
            }
            $encuesta->empresas_id = $this->e_empresas_id;
            $encuesta->encuesta = $this->e_encuesta;
            $encuesta->edicion = $this->e_edicion;
            $encuesta->opcionesxcol = $this->e_opcionesxcol;
            $encuesta->habilitada = $this->e_habilitada ? 1 : 0;
            $encuesta->save();
        } catch (Throwable $e) {
            // dd($e->getMessage());
            $msg = $e->getMessage();
            session()->flash('error', 'no se ha podido guardar los datros, error de integridad.');
        }
        // dd("va po el back");
        //return back()->with(['success' => "Se creo o modificó correctamente la cabecera de la encuesta."]);
    }

    public function periodo_store()
    {
        try {
            if (empty($this->periodos_id_modif)) {
                $periodos = new periodo();
            } else {
                $periodos = periodo::where("id", $this->periodos_id_modif);
            }
            $periodos->encuestas_id = $this->encuestas_id_selected;
            $periodos->descrip_rango = $this->p_descrip_rango;
            $periodos->desde = $this->p_desde;
            $periodos->hasta = $this->p_hasta;
            $periodos->habilitada = $this->p_habilitada;
            $periodos->save();
        } catch (Throwable $e) {
            // dd($e->getMessage());
            $msg = $e->getMessage();
            session()->flash('error', 'no se ha podido guardar los datros, error de integridad.');

        }
       // return back()->with(['success' => "Se creo o modificó correctamente un periodo de la encuesta."]);
    }

    public function opcion_store()
    {
        $validatedData = $this->validate(
                ['encuestas_id_selected' => 'required',
                'o_opciones_id' => 'required',
                'o_puntos' => ['required','integer', "max:99"],
                'o_orden' => ['required','integer', "max:99"],
                'o_style' => ['required', 'max:200]']],
                ['encuestas_id_selected.required' => 'Debe haber seleccionado una encuesta para poder cargar opciones.',
                'o_opciones_id.required' => 'Es obligatorio seleccionar una opción de la lista.']

            );

        try {
            if (empty($this->opciones_id_modif)) {
                $encuestas_opciones = new encuesta_opcion();
            } else {
                $encuestas_opciones = encuesta_opcion::where("id", $this->opciones_id_modif);
            }
            $encuestas_opciones->encuestas_id = $this->encuestas_id_selected;
            $encuestas_opciones->opciones_id = $this->o_opciones_id;
            $encuestas_opciones->puntos = $this->o_puntos;
            $encuestas_opciones->orden = $this->o_orden;
            $encuestas_opciones->style = $this->o_style;
            $encuestas_opciones->save();
        } catch (Throwable $e) {
            // dd($e->getMessage());
            $msg = $e->getMessage();
            return back()->with(['danger' => $msg]);
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

    public function selectTab($tab){
        $this->currentTab = $tab;
    }

    public function borrar_encuesta($idEncuesta) {
        $msg = "";
        try {
            Encuesta::where("id", $idEncuesta)->delete();
        
        } catch (Throwable $e) {
            // dd($e->getMessage());
            $msg = $e->getMessage();

        }
        session()->flash('message', 'Encuesta borrada correctamente. <br/>' . $msg );
    }
}
