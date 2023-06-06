<?php

namespace App\Http\Controllers\encuestas;

use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReconocimientosController extends Controller
{
    public function recibidos(){

        $recibidos = DB::select('select * from v_reconocimientos_recibidos where id_recibido = ' . Auth()->user()->id);
        $titulo = 'Reconocimientos recibidos';
        
        return view('encuestas.recibidos', compact('recibidos', 'titulo'));
    }

    public function realizados(){

        $realizados = DB::select('select * from v_reconocimientos_realizados where users_id = ' . Auth()->user()->id);
        $titulo = 'Reconocimientos realizados';
        
        return view('encuestas.realizados', compact('realizados', 'titulo'));
    }
}
