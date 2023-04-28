<?php

namespace App\Http\Controllers\encuestas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Encuesta;
 
class EncuestaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $encuesta = Encuesta::v_encuesta_actual()->get();
var_dump($encuesta);
        return view('encuestas.encuesta', compact('encuesta'));
    }
       
}
