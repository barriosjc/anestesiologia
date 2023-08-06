<?php

namespace App\Http\Controllers\encuestas;

use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ReconocimientosRealizadosExport;
use Maatwebsite\Excel\Facades\Excel;

class ReconocimientosController extends Controller
{
    public $query = "";

    public function recibidos($tipo){

        if ($tipo === 'user') {
            $recibidos = DB::select('select * from v_reconocimientos_recibidos where id_recibido = ' . Auth()->user()->id);
            $titulo = 'Reconocimientos recibidos';
        } else {
            $recibidos = DB::select('select * from v_reconocimientos_recibidos where empresas_id = ' . session('empresa')->id) ;
            $titulo = 'Reconocimientos';
        }

        return view('encuestas.recibidos', compact('recibidos', 'titulo', 'tipo'));
    }

    public function realizados($tipo){
        $periodo_id = null;
        $realizados = [];
        $titulo = null;
        if(isset($_GET['periodo_id'])) {
            $sql = "select p.id, p.desde, p.hasta from periodos p 
                          WHERE p.id = " .$_GET['periodo_id'] ;
        } else { 
            $sql = 'select p.id, p.desde, p.hasta from periodos p 
                                    inner join encuestas en on en.id = p.encuestas_id 
                                    INNER JOIN empresas e ON e.id = en.empresas_id
                                    and e.id = ' . session('empresa')->id . 
                        "  WHERE p.desde <= DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                        AND p.hasta >= DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                        AND p.habilitada = 1
                        AND en.habilitada = 1
                        AND p.deleted_at is null
                        AND e.deleted_at is null" ;
        }
        // dd($sql);
        $data = DB::select($sql); 
        if (empty($data)) {
            $periodo_id = 0 ;     //no hay periodo activo
        } else {
            $periodo_id = $data[0]->id;
            $desde = $data[0]->desde;
            $hasta = $data[0]->hasta;
        }  
        
        if ($periodo_id) {
            if ($tipo === 'user') {
                $query = 'select * from v_reconocimientos_realizados where users_id = ' . Auth()->user()->id 
                            . " and fecha_ing >= '" . $desde ."' and fecha_ing <= '" . $hasta ."'"
                            . " and periodos_id = " . $periodo_id;
                $realizados = DB::select($query);
                $titulo = 'Reconocimientos realizados';
            } else {
                $filtro = " empresas_id = " . session('empresa')->id;
                $filtro = $filtro . " and fecha_ing >= '" . $desde ."' and fecha_ing <= '" . $hasta ."'";
                $filtro = $filtro . " and periodos_id = " . $periodo_id;
                $query = 'select * from v_reconocimientos_realizados where ' . $filtro
                            . ' order by fecha_ingreso desc';

                $realizados = DB::select($query) ;
                $titulo = 'Reconocimientos';
            }
            // dd($query);
            session(['query_reconocimientos' => $query]);  
        }
        
        $periodos = DB::select('select p.id, CONCAT(e.encuesta, " - ", p.descrip_rango," - ", 
                                    DATE_FORMAT(p.desde, "%d/%m/%Y") ," - ", DATE_FORMAT(p.hasta, "%d/%m/%Y")) descripcion 
                                from periodos p 
                                    inner join encuestas e on e.id = p.encuestas_id 
                                    and p.habilitada = 1 
                                    and e.habilitada = 1
                                    and empresas_id  = ' . session('empresa')->id .
                                    ' and p.deleted_at is null
                                    and e.deleted_at is null
                                  order by p.hasta desc');

        return view('encuestas.realizados', compact('realizados', 'titulo', 'tipo', 'periodos','periodo_id'));
    }

    public function export($tipo) {
        return Excel::download(new ReconocimientosRealizadosExport(), 'reconocimientos.xlsx');
        // fuerza un tipo de salida return Excel::download(new ReconocimientosRealizadosExport, 'reconocimientos.xlsx', \Maatwebsite\Excel\Excel::CSV);
    }

}


