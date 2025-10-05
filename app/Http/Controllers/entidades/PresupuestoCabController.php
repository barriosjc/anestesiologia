<?php

namespace App\Http\Controllers\entidades;

use App\Models\Centro;
use App\Models\Parametro;
use App\Models\Profesional;
use App\Models\Gerenciadora;
use Illuminate\Http\Request;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PresupuestoCabRequest;

class PresupuestoCabController extends Controller
{
public function index()
{
    $presupuestosCab = PresupuestoCab::withSum('presupuestosDet as total', 'valor')
        ->paginate(10);

    return view('presupuestos.presupuestos_cab.index', compact('presupuestosCab'));
}


    public function create()
    {
        $uds = Parametro::where('nombre', 'UDS')->first()->valor;
        $centros = Centro::all();
        $profesionales = Profesional::orderBy('nombre')->get();
        $presupuestosCab = new PresupuestoCab();
        $gerenciadoras = Gerenciadora::all();

        return view('presupuestos.presupuestos_cab.create', compact('presupuestosCab', 'centros', 'profesionales', 'uds', 'gerenciadoras')); 
    }

    public function edit(int $id)
    {
        $centros = Centro::all();
        $gerenciadoras = Gerenciadora::all();
        $profesionales = Profesional::orderBy('nombre')->get();
        $presupuestosCab = PresupuestoCab::find($id);

        return view('presupuestos.presupuestos_cab.create', compact('gerenciadoras', 'presupuestosCab', 'centros', 'profesionales'));
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
            'gerenciadora_id'=> $request->gerenciadora_id,
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

    public function print(int $id)
    {
        $presupuesto = PresupuestoCab::with(['pagos', 'centro', 'user'])->findOrFail($id);
        $query1 = PresupuestoDet::query()
            ->join('nom_practicas_estudios as pe', function ($join) {
                $join->on('presupuestos_det.nom_padre_id', '=', 'pe.nom_padre_id')
                    ->on('presupuestos_det.nomenclador_id', '=', 'pe.id');
            })
            ->where('presupuestos_det.presupuesto_cab_id', $id)
            ->select('presupuestos_det.*', 'pe.nombre as descripcion');

        $query2 = PresupuestoDet::query()
            ->join('nomenclador as n', function ($join) {
                $join->on('presupuestos_det.nom_padre_id', '=', 'n.nom_padre_id')
                    ->on('presupuestos_det.nomenclador_id', '=', 'n.id');
            })
            ->where('presupuestos_det.presupuesto_cab_id', $id)
            ->select('presupuestos_det.*', 'n.descripcion as descripcion');
        $detalles = $query1->unionAll($query2)->get();
        $presupuesto->presupuestosDet = $detalles;

        $pdf = Pdf::loadView('presupuestos.presupuestos_cab.informe', compact('presupuesto'));

        //return $pdf->stream('presupuesto_'.$presupuesto->id.'.pdf');
        // Si querés que se descargue automáticamente:
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="presupuesto_'.$presupuesto->id.'.pdf"');
    }
}
