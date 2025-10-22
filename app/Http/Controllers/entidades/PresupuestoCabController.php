<?php

namespace App\Http\Controllers\entidades;

use App\Models\Centro;
use App\Models\Parametro;
use App\Models\Profesional;
use App\Models\Gerenciadora;
// use Illuminate\Http\Request;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PresupuestoCabRequest;
use App\Models\Consumo_cab;
use App\Models\Consumo_det;
use App\Models\Paciente;
use App\Models\Parte_cab;

class PresupuestoCabController extends Controller
{
    public function index()
    {
        $presupuestosCab = DB::table('v_presupuestos_cab')->paginate(10);

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

    public function partes(int $id)
    {
        $presup_det = PresupuestoDet::where('presupuesto_cab_id', $id)
                    ->orderBy('cobertura_id', 'asc')
                    ->get();

        foreach ($presup_det as $item) {
            $presupuesto = PresupuestoCab::where('id', $id)->first();
            $paciente = Paciente::firstOrCreate(
                ['dni' => $presupuesto->dni],
                ['nombre' => $presupuesto->nombre, 'fec_nacimiento' => $presupuesto->fecha_nac]
            );
            $parte = new Parte_cab();
            $parte->profesional_id = $presupuesto->profesional_id;
            $parte->paciente_id =  $paciente->id;
            $parte->gerenciadora_id = $presupuesto->gerenciadora_id;
            $parte->cobertura_id = $item->cobertura_id;
            $parte->centro_id = $presupuesto->centro_id;
            $parte->fec_presentacion = $presupuesto->fecha;
            $parte->fec_presentacion_fin = $presupuesto->fecha;
            $parte->user_id = $presupuesto->usuario_id;
            $parte->observaciones = $presupuesto->observaciones;
            $parte->estado_id = 1;
            $parte->save();

            $consumo = new Consumo_cab();
            $consumo->parte_cab_id = $parte->id;
            $consumo->user_id = $parte->user_id;
            $consumo->save();

            $cons_det = new Consumo_det();
            $cons_det->consumo_cab_id = $consumo->id;
            $cons_det->nomenclador_id = $presup_det->nomenclador_id;
            $cons_det->porcentaje = $presup_det->porcentaje;
            $cons_det->cantidad = 1;
            $cons_det->valor = $presup_det->valor;
            $cons_det->periodo = $presup_det->periodo;
            $cons_det->estado_id = 1;
            $cons_det->obs_refac = $presup_det->observaciones;
            $cons_det->nom_padre_id =$presup_det->nom_padre_id;
            $cons_det->save();
        }
    }
}
