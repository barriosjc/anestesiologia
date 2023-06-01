<?php

namespace App\Http\Controllers\encuestas;

use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReconocimientosController extends Controller
{
    public function recibidos(){

        view('encuestas.recibidos');
    }

    public function realizados(){

        $realizados = DB::select('select * from v_reconocimientos_realizados where users_id = ' . Auth()->user()->id);
        $titulo = 'Reconocimientos realizados';
        
        return view('encuestas.realizados', compact('realizados', 'titulo'));
    }
}
