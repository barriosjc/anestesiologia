<?php

namespace App\Http\Controllers\entidades;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PresupuestoCabController extends Controller
{
    public function index()
    {
        return view('entidades.presupuesto_cab.index');
    }
}
