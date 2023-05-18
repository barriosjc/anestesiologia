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
    public $p_habilitada = null;
    //opciones
    public $opciones_id_modif = null;
    public $o_opciones_id = null;
    public $o_puntos = null;
    public $o_orden = null;
    public $o_style = null;
    public $o_habilitada= null;

    public function render()
    {
        $titulo = "Alta de una encuesta";
        $cant_per = null;
        $cant_opc = null;
        if (Auth()->user()->empresas_id === 0) {
        $empresas = empresa::all();
        $encuestas = Encuesta::v_encuestas()
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
    public function create()
    {
        $titulo = "Alta de una encuesta";
        $opciones = Opcion::all();
        $empresas = empresa::all();
        $periodos = Periodo::all();
        $encuestas_opciones = encuesta_opcion::v_encuestas_opciones()->get();
        //DB::connection()->enableQueryLog();
        $encuestas = Encuesta::v_encuestas()->get();
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
    
    public function encuesta_store()
    {
        $validatedData = $this->validate([
            'e_empresas_id' => 'required',
            'e_encuesta' => ['required', "max:50"],
            'e_edicion' => ['required', "max:200"],
            'e_opcionesxcol' => ['required', 'integer','max:5]']],
        );

        try {
            if (empty($this->encuestas_id_modif)) {
                $encuesta = new Encuesta();
            } else {
                $encuesta = Encuesta::where("id", $this->encuestas_id_modif)->first();
            }
            $encuesta->empresas_id = $this->e_empresas_id;
            $encuesta->encuesta = $this->e_encuesta;
            $encuesta->edicion = $this->e_edicion;
            $encuesta->opcionesxcol = $this->e_opcionesxcol;
            $encuesta->habilitada = $this->e_habilitada ? 1 : 0;
            $encuesta->save();
            
            $this->encuesta_limpiar();
            $this->resetErrorBag();
            session()->flash('message', "Los datos de la encuesta se guardaron conrrectamente.");

        } catch (Throwable $e) {
            // dd($e->getMessage());
            $msg = $e->getMessage();
            session()->flash('error', $msg .'     encuestas se ha podido guardar los datros, error de integridad. Estos datos no se pueden repetir empresa, nombre, ecición');
        }
    }

    public function editar_encuesta($encuestas_id) {

        $encuesta = encuesta::where('id', $encuestas_id)->first();
        $this->e_empresas_id = $encuesta->empresas_id;        
        $this->e_encuesta = $encuesta->encuesta;
        $this->e_edicion = $encuesta->edicion;
        $this->e_opcionesxcol = $encuesta->opcionesxcol;        
        $this->e_habilitada = $encuesta->habilitada;
        $this->encuestas_id_modif = $encuestas_id;
    }

    public function encuesta_limpiar(){
        $this->reset(['encuestas_id_modif',
                    'encuestas_id_selected',
                    'e_empresas_id',
                    'e_encuesta',
                    'e_edicion',
                    'e_opcionesxcol',
                    'e_habilitada']);
    }

    public function borrar_encuesta($idEncuesta) {
        $msg = "";
        try {
            Encuesta::where("id", $idEncuesta)->delete();
        
        } catch (Throwable $e) {
            // dd($e->getMessage());
            $msg = $e->getMessage();

        }
        session()->flash('message', 'Encuesta borrada correctamente.' );
    }

    //  -------------------------------------------------------------------------------------------------------------
    public function periodo_store()
    {
        try {
            if (empty($this->periodos_id_modif)) {
                $periodos = new periodo();
            } else {
                $periodos = periodo::where("id", $this->periodos_id_modif)->first();
            }
            $periodos->encuestas_id = $this->encuestas_id_selected;
            $periodos->descrip_rango = $this->p_descrip_rango;
            $periodos->desde = $this->p_desde;
            $periodos->hasta = $this->p_hasta;
            $periodos->habilitada =  $this->p_habilitada ? 1 : 0;
            $periodos->save();
            $this->periodo_limpiar();
            $this->resetErrorBag();
            session()->flash('message', "Los datos del periodo se guardaron conrrectamente.");

        } catch (Throwable $e) {
            // dd($e->getMessage());
            $msg = $e->getMessage();
            session()->flash('error', 'no se ha podido guardar los datros, error de integridad. no se puede repetir. '.$msg);

        }
       // return back()->with(['success' => "Se creo o modificó correctamente un periodo de la encuesta."]);
    }

    public function editar_periodo($periodos_id) {

        $periodo = periodo::where('id', $periodos_id)->first();
        $this->p_encuestas_id = $periodo->encuestas_id;
        $this->p_descrip_rango = $periodo->descrip_rango;
        $this->p_desde = $periodo->desdey;
        $this->p_hasta = $periodo->hastay;
        $this->p_habilitada = $periodo->habilitada;
        $this->periodos_id_modif = $periodos_id;

    }

    public function periodo_limpiar(){
        $this->reset(['periodos_id_modif',
                    'p_descrip_rango',
                    'p_desde',
                    'p_hasta',
                    'p_habilitada']);
    }

    public function borrar_periodo($idPeriodo) {
        $msg = "";
        try {
            Periodo::where("id", $idPeriodo)->delete();
        
        } catch (Throwable $e) {
            // dd($e->getMessage());
            $msg = $e->getMessage();

        }
        session()->flash('message', 'Periodo borrado correctamente.'  );
    }

    //  -------------------------------------------------------------------------------------------------------
    public function opcion_store()
    {
        $validatedData = $this->validate(
                ['encuestas_id_selected' => 'required',
                'o_opciones_id' => 'required',
                'o_puntos' => ['required','integer', "max:99"],
                'o_orden' => ['required','integer', "max:99"],
                'o_style' => ['required', 'max:200']],
                ['encuestas_id_selected.required' => 'Debe haber seleccionado una encuesta para poder cargar opciones.',
                'o_opciones_id.required' => 'Es obligatorio seleccionar una opción de la lista.']
            );

        try {
            if (empty($this->opciones_id_modif)) {
                $encuestas_opciones = new encuesta_opcion();
            } else {
                $encuestas_opciones = encuesta_opcion::where("id", $this->opciones_id_modif)->first();
            }
            $encuestas_opciones->encuestas_id = $this->encuestas_id_selected;
            $encuestas_opciones->opciones_id = $this->o_opciones_id;
            $encuestas_opciones->puntos = $this->o_puntos;
            $encuestas_opciones->orden = $this->o_orden;
            $encuestas_opciones->style = $this->o_style;
            $encuestas_opciones->habilitada =  $this->o_habilitada ? 1 : 0;
            $encuestas_opciones->save();
            $this->opcion_limpiar();
            $this->resetErrorBag();
            session()->flash('message', "Los datos de la opción se guardaron conrrectamente.");

        } catch (Throwable $e) {
            // dd($e->getMessage());
            $msg = $e->getMessage();
            return back()->with(['danger' => $msg]);
        }
        return back()->with(['success' => "Se creo o modificó correctamente una opción para la encuesta."]);
    }

    public function editar_opcion($opciones_id) {
        $opcion = encuesta_opcion::where('id', $opciones_id)->first();
        $this->o_opciones_id = $opcion->opciones_id;
        $this->o_puntos = $opcion->puntos;
        $this->o_orden = $opcion->orden;
        $this->o_style = $opcion->style;
        $this->o_habilitada = $opcion->habilitada;
        $this->opciones_id_modif = $opciones_id;
    }

    public function opcion_limpiar(){
        $this->reset(['opciones_id_modif',
                    'o_opciones_id',
                    'o_puntos',
                    'o_orden',
                    'o_style',
                ]);
    }

    public function borrar_opcion($idOpcion) {
        $msg = "";
        try {
            Opcion::where("id", $idOpcion)->delete();
        
        } catch (Throwable $e) {
            // dd($e->getMessage());
            $msg = $e->getMessage();

        }
        session()->flash('message', 'Opción borrada correctamente.' );
    }

    public function selectTab($tab){
        $this->currentTab = $tab;
    }

}