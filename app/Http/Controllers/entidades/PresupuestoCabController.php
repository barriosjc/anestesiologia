<?php

namespace App\Http\Controllers\entidades;

use App\Models\User;
use App\Models\Centro;
use App\Models\Paciente;
use App\Models\Parametro;
use App\Models\Parte_cab;
use App\Models\Consumo_cab;
use App\Models\Consumo_det;
use App\Models\Profesional;
use App\Models\Gerenciadora;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\PresupuestoCabRequest;

class PresupuestoCabController extends Controller
{
    public function index()
    {
        $centros = Centro::all();
        $profesionales = Profesional::orderBy('nombre')->get();
        $gerenciadoras = Gerenciadora::all();
        $usuarios = User::all();
        $presupuestosCab = DB::table('v_presupuestos_cab')->paginate(10);

        return view('presupuestos.presupuestos_cab.index', compact('presupuestosCab', 'centros', 'profesionales', 'gerenciadoras', 'usuarios'));
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
        if (empty($presupuestosCab)) {
            return redirect()->back()->with(["error" => "No es posible editar un presupuesto dado de baja."]);
        }

        return view('presupuestos.presupuestos_cab.create', compact('gerenciadoras', 'presupuestosCab', 'centros', 'profesionales'));
    }

    public function store(PresupuestoCabRequest $request)
    {
        $presupuestosCab = PresupuestoCab::updateOrCreate(['id' => $request['id']], [
            'fecha'            => $request->fecha,
            'nombre'           => $request->nombre,
            'fecha_nac' => $request->fecha_nac,
            'dni'              => $request->dni,
            'centro_id'        => $request->centro_id,
            'observaciones'    => $request->observaciones,
            'usuario_id'       => Auth::id(),
            'profesional_id'   => $request->profesional_id,
            'valor_dolar'      => $request->valor_dolar,
            'estado'           => 'I',
            'gerenciadora_id'  => $request->gerenciadora_id,
        ]);

        return redirect()->route('presupuestos.det.create', ['id' => $presupuestosCab->id]);
    }

    public function update(PresupuestoCabRequest $request, PresupuestoCab $presupuesto)
    {
        $presupuesto->update($request->validated());

        return redirect()->route('presupuestos.cab.index')->with('success', 'Presupuesto actualizado correctamente.');
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
            ->header('Content-Disposition', 'inline; filename="presupuesto_' . $presupuesto->id . '.pdf"');
    }

    public function partes(int $id)
    {
        $presupuesto = PresupuestoCab::where('id', $id)->first();

        // Validar ANTES del loop
        $validation = $this->validateCab($presupuesto);
        if ($validation !== true) {
            return $validation; // Retornar el redirect si falla
        }

        $presup_det = PresupuestoDet::where('presupuesto_cab_id', $id)
            ->orderBy('cobertura_id', 'asc')
            ->get();

        $tmp_cobertura = 0;
        $consumo = null; // Inicializar fuera del loop

        foreach ($presup_det as $item) {
            if ($item->cobertura_id != $tmp_cobertura) {
                $tmp_cobertura = $item->cobertura_id;

                $paciente = Paciente::firstOrCreate(
                    ['dni' => $presupuesto->dni],
                    ['nombre' => $presupuesto->nombre, 'fec_nacimiento' => $presupuesto->fecha_nac]
                );

                $parte = new Parte_cab();
                $parte->profesional_id = $presupuesto->profesional_id;
                $parte->paciente_id = $paciente->id;
                $parte->gerenciadora_id = $presupuesto->gerenciadora_id;
                $parte->cobertura_id = $item->cobertura_id;
                $parte->centro_id = $presupuesto->centro_id;
                $parte->fec_prestacion = $presupuesto->fecha;
                $parte->fec_prestacion_fin = $presupuesto->fecha;
                $parte->user_id = $presupuesto->usuario_id;
                $parte->observaciones = $presupuesto->observaciones;
                $parte->estado_id = 4;
                $parte->save();

                $consumo = new Consumo_cab();
                $consumo->parte_cab_id = $parte->id;
                $consumo->user_id = $parte->user_id;
                $consumo->save();

                $presupuesto->parte_cab_id = $parte->id;
                $presupuesto->save();
            }

            $cons_det = new Consumo_det();
            $cons_det->consumo_cab_id = $consumo->id;
            $cons_det->nomenclador_id = $item->nomenclador_id; // Era $presup_det, debe ser $item
            $cons_det->porcentaje = $item->porcentaje;
            $cons_det->cantidad = 1;
            $cons_det->valor = $item->valor;
            $cons_det->periodo = $item->periodo;
            $cons_det->estado_id = 4;
            $cons_det->obs_refac = $item->observaciones;
            $cons_det->nom_padre_id = $item->nom_padre_id;
            $cons_det->save();
        }

        // Retornar algo al finalizar exitosamente
        return redirect()->back()->with('success', 'Partes creados correctamente. Nro de parte generado: ' . $parte->id);
    }

    private function validateCab($presupuesto)
    {
        if ($presupuesto->parte_cab_id !== null) {
            return redirect()
                ->back()
                ->with('error', 'Ya se ha generado el parte, datos de facturación para este presupuesto.');
        }

        $validatorCab = Validator::make($presupuesto->toArray(), [
            'profesional_id' => 'required|integer',
            'gerenciadora_id' => 'required|integer',
            'centro_id' => 'required|integer',
            'usuario_id' => 'required|integer',
            'fecha' => 'required|date',
            'dni' => 'required|string',
            'nombre' => 'required|string',
            'fecha_nac' => 'required|date|before_or_equal:today'
        ], [
            'profesional_id.required' => 'Falta el profesional.',
            'gerenciadora_id.required' => 'Falta la gerenciadora.',
            'centro_id.required' => 'Falta el centro.',
            'usuario_id.required' => 'Falta el usuario.',
            'fecha.required' => 'Falta la fecha del presupuesto.',
            'dni.required' => 'Falta el DNI del paciente.',
            'nombre.required' => 'Falta el nombre del paciente.',
            'fecha_nac.required' => 'En la cabecera de presupuesto debe ingresda la fecha de naciento del paciente.',
            'fecha_nac.before_or_equal' => 'La fecha de nacimiento no puede ser posterior a fecha de hoy.'
        ]);

        if ($validatorCab->fails()) {
            return redirect()->back()
                ->withErrors($validatorCab)
                ->withInput()
                ->with('error', 'Hay campos obligatorios vacíos en la cabecera.');
        }
        return true; // Retornar true si la validación pasa
    }

    public function pagado($id)
    {
        $presupuestosCab = PresupuestoCab::where('id', $id)->first();
        $presupuestosCab->estado = "p";
        $presupuestosCab->save();

        return redirect()->back();
    }

    public function filtrar(Request $request)
    {
        // $query = PresupuestoCab::query();
        $query = DB::table('v_presupuestos_cab');

        // Aplicar filtros
        if ($request->filled('centro')) {
            $query->where('centro_id', $request->centro);
        }

        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', "%{$request->nombre}%");
        }

        if ($request->filled('profesional')) {
            $query->where('profesional_id', $request->profesional);
        }

        if ($request->filled('usuario')) {
            $query->where('usuario_id', $request->usuario);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Obtener resultados paginados
        $presupuestosCab = $query->orderBy('fecha', 'desc')->paginate(15);

        $centros = Centro::get();
        $profesionales = Profesional::orderBy('nombre')->get();
        $gerenciadoras = Gerenciadora::orderBy('nombre')->get();
        $usuarios = User::get();
        // Estados (asumiendo que ya los tienes definidos)
        $estados = [
            'pendiente' => ['texto' => 'Pendiente', 'clase' => 'bg-warning'],
            'pagado' => ['texto' => 'Pagado', 'clase' => 'bg-success'],
            'cancelado' => ['texto' => 'Cancelado', 'clase' => 'bg-danger'],
            // Agrega más estados según necesites
        ];

        return view('presupuestos.presupuestos_cab.index', compact('presupuestosCab', 'centros', 'profesionales', 'gerenciadoras', 'usuarios'));

    }
}
