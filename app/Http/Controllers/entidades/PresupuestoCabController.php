<?php

namespace App\Http\Controllers\entidades;

use App\Models\Centro;
use Illuminate\Http\Request;
use App\Models\PresupuestoCab;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PresupuestoCabRequest;
use App\Models\Profesional;

class PresupuestoCabController extends Controller
{
    public function index()
    {
        $presupuestosCab = PresupuestoCab::paginate(10);

        return view('presupuestos.presupuestos_cab.index', compact('presupuestosCab'));
    }

    public function create()
    {
        $centros = Centro::all();
        $profesionales = Profesional::orderBy('nombre')->get();
        $presupuestosCab = new PresupuestoCab();

        return view('presupuestos.presupuestos_cab.create', compact('presupuestosCab', 'centros', 'profesionales'));
    }

    public function edit(int $id)
    {
        $centros = Centro::all();
        $profesionales = Profesional::orderBy('nombre')->get();
        $presupuestosCab = PresupuestoCab::find($id);

        return view('presupuestos.presupuestos_cab.create', compact('presupuestosCab', 'centros', 'profesionales'));
    }

    public function store(PresupuestoCabRequest $request)
    {
        $presupuestosCab = PresupuestoCab::updateOrCreate(['id' => $request['id']],[
            'fecha'          => $request->fecha,
            'nombre'         => $request->nombre,
            'fecha_nac'      => $request->fecha_nac,
            'dni'            => $request->dni,
            'centro_id'      => $request->centro_id,
            'observaciones'  => $request->observaciones,
            'usuario_id'     => Auth::id(),
            'profesional_id' => $request->profesional_id,
            'valor_dolar'    => $request->valor_dolar,
            'estado'         => 'I',
        ]);
    
        return redirect()->route('presupuestos.det.create', ['id' => $presupuestosCab->id]);
    }
    
    public function update(PresupuestoCabRequest $request, PresupuestoCab $presupuesto)
    {
        $presupuesto->update($request->validated());
    
        return redirect()->route('presupuestos.index')->with('success', 'Presupuesto actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $presupuesto = PresupuestoCab::findOrFail($id);
        $presupuesto->delete();

        return redirect()->route('presupuestos.cab.index')->with('success', 'Presupuesto eliminado correctamente.');
    }
}
