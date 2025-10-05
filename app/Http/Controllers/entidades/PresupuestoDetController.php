<?php

namespace App\Http\Controllers\entidades;

use App\Models\Periodo;
use App\Models\Cobertura;
use Illuminate\Http\Request;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConsumoDetRequest;
use App\Models\GerenciadoraCoberturaNomPadre;

class PresupuestoDetController extends Controller
{
    public function create(int $presupuestosCabId)
    {
        $presupuestosCab = PresupuestoCab::findOrFail($presupuestosCabId);
        $query1 = PresupuestoDet::query()
            ->join('nom_practicas_estudios as pe', function ($join) {
                $join->on('presupuestos_det.nom_padre_id', '=', 'pe.nom_padre_id')
                    ->on('presupuestos_det.nomenclador_id', '=', 'pe.id');
            })
            ->where('presupuestos_det.presupuesto_cab_id', $presupuestosCabId)
            ->select('presupuestos_det.*', 'pe.nombre as descripcion');

        $query2 = PresupuestoDet::query()
            ->join('nomenclador as n', function ($join) {
                $join->on('presupuestos_det.nom_padre_id', '=', 'n.nom_padre_id')
                    ->on('presupuestos_det.nomenclador_id', '=', 'n.id');
            })
            ->where('presupuestos_det.presupuesto_cab_id', $presupuestosCabId)
            ->select('presupuestos_det.*', 'n.descripcion as descripcion');

        $presupuestosDet = $query1->unionAll($query2)->get();
        $coberturas = Cobertura::all();
        $periodos = Periodo::all();

        // $array = GerenciadoraCoberturaNomPadre::where('gerenciadora_id', $presupuestosCab->gerenciadora_id)
        // ->where('cobertura_id', $presupuestosCab->cobertura_id)
        // ->pluck('nom_padre_id')
        // ->toArray();
        // $nom_padre_json = json_encode($array);


        return view('presupuestos.presupuestos_det.create', compact('periodos', 'presupuestosCab', 'coberturas', 'presupuestosDet'));
    }

    public function store(ConsumoDetRequest $request)
    {
        $presupuestosDet = new PresupuestoDet($request->all());
        $presupuestosDet->save();

        return redirect()->back()->with('success', 'La operación se ha completado exitosamente.');
    }

    public function destroy($presupuestoCabId, $id)
    {
        $presupuestosDet = PresupuestoDet::findOrFail($id);
        $presupuestosDet->delete();

        return redirect()->route('presupuestos.det.create', ['id' => $presupuestoCabId])
            ->with('success', 'El registro ha sido eliminado exitosamente.');
    }
}
