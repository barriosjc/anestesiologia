<?php

namespace App\Http\Controllers\entidades;

use App\Models\Cobertura;
use Illuminate\Http\Request;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use App\Http\Controllers\Controller;
use App\Models\Periodo;

class PresupuestoDetController extends Controller
{
    public function create(int $presupuestosCabId)
    {
        $presupuestosCab = PresupuestoCab::findOrFail($presupuestosCabId);
        $presupuestosDet = PresupuestoDet::where('presupuesto_cab_id', $presupuestosCabId)->get();
        $coberturas = Cobertura::all();
        $periodos = Periodo::all();


        return view('presupuestos.presupuestos_det.create', compact('periodos', 'presupuestosCab', 'coberturas', 'presupuestosDet'));
    }
}
