<?php

namespace App\Http\Controllers\entidades;

use App\Models\Periodo;
use App\Models\Cobertura;
use Illuminate\Http\Request;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use App\Http\Controllers\Controller;
use App\Models\GerenciadoraCoberturaNomPadre;

class PresupuestoDetController extends Controller
{
    public function create(int $presupuestosCabId)
    {
        $presupuestosCab = PresupuestoCab::findOrFail($presupuestosCabId);
        $presupuestosDet = PresupuestoDet::where('presupuesto_cab_id', $presupuestosCabId)->get();
        $coberturas = Cobertura::all();
        $periodos = Periodo::all();

        // $array = GerenciadoraCoberturaNomPadre::where('gerenciadora_id', $presupuestosCab->gerenciadora_id)
        // ->where('cobertura_id', $presupuestosCab->cobertura_id)
        // ->pluck('nom_padre_id')
        // ->toArray();
        // $nom_padre_json = json_encode($array);


        return view('presupuestos.presupuestos_det.create', compact('periodos', 'presupuestosCab', 'coberturas', 'presupuestosDet'));
    }

    public function store(Request $request)
    {
        // seguir con esto , crear el validador y reemplazar el parametro
        $presupuestosDet = new PresupuestoDet($request->all());
        $presupuestosDet->save();

        return redirect()->back()->with('success', 'La operación se ha completado exitosamente.');
    }
}
