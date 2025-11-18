<?php

namespace App\Http\Controllers\entidades;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoPago;

class PresupuestoPagosController extends Controller
{
    public function create(int $id)
    {
        $pagos = PresupuestoPago::where('presupuesto_cab_id', $id)->get();
        $presupuestosCab = PresupuestoCab::where('id', $id)->first();

        return view('presupuestos.presupuestos_pagos.create', compact('pagos', 'presupuestosCab'));
    }

    public function store(Request $request)
    {
        $pagos = new PresupuestoPago($request->all());
        $pagos->save();

        return redirect()->back()->with('success', 'La operación se ha completado exitosamente.');
    }
    
    public function destroy($presupuestoCabId, $id)
    {
        $pago = PresupuestoPago::findOrFail($id);
        $pago->delete();

        return redirect()->route('presupuestos.pagos.create', ['id' => $presupuestoCabId])
            ->with('success', 'El pago ha sido eliminado exitosamente.');
    }
}
