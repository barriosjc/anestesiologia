<?php

namespace App\Http\Controllers\encuestas;

use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ReconocimientosRealizadosExport;
use Maatwebsite\Excel\Facades\Excel;

class ReconocimientosController extends Controller
{
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

        if ($tipo === 'user') {
            $realizados = DB::select('select * from v_reconocimientos_realizados where users_id = ' . Auth()->user()->id);
            $titulo = 'Reconocimientos realizados';
        } else {
            $realizados = DB::select('select * from v_reconocimientos_realizados where empresas_id = ' . session('empresa')->id);
            $titulo = 'Reconocimientos';
        }
       
        return view('encuestas.realizados', compact('realizados', 'titulo', 'tipo'));
    }

    public function export($tipo) {
        return Excel::download(new ReconocimientosRealizadosExport, 'reconocimientos.xlsx');
        // fuerza un tipo de salida return Excel::download(new ReconocimientosRealizadosExport, 'reconocimientos.xlsx', \Maatwebsite\Excel\Excel::CSV);
    }

}


