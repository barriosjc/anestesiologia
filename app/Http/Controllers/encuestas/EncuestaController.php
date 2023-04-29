<?php

namespace App\Http\Controllers\encuestas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Encuesta;
use App\Models\Grupal;
use App\Models\User;

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
        //traer todos los usuarios de la empresa y excluye al users_id
        $users = User::all();    
        //traer todos los grupos de la empresa del usuario
        $grupal = Grupal::all();
         
        return view('encuestas.encuesta', compact('encuesta', 'users', 'grupal'));
    }
       
}
