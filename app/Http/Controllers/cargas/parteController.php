<?php

namespace App\Http\Controllers\cargas;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Centro;
use App\Models\Estado;
use App\Models\Paciente;
use App\Models\Cobertura;
use App\Models\Documento;
use App\Models\Parte_cab;
use App\Models\Parte_det;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ParteController extends Controller
{
    // public function index()
    // {
    //     $coberturas = Cobertura::orderby("nombre")->get();
    //     $centros = Centro::orderby("nombre")->get();
    //     $profesionales = Profesional::orderby("nombre")->get();
    //     $estados = Estado::get();
    //     $users = User::get();

    //     $partes = parte_cab::v_parte_cab()
    //         ->where("centro_id", Auth()->user()->centro_id)
    //         ->orderBy("id", "desc")
    //         ->paginate();

    //     return view('cargas.cab.parte', compact(
    //         'partes',
    //         "coberturas",
    //         "centros",
    //         "profesionales",
    //         "estados",
    //         "users"
    //     ));
    //                 // ->with('i', (request()->input('page', 1) - 1) * $partes->perPage());
    // }

    public function filtrar(Request $request)
    {
        // dd($request->all());
        $coberturas = Cobertura::orderby("nombre")->get();
        $centros = Centro::orderby("nombre")->get();
        $profesionales = Profesional::get();
        $estados = Estado::get();
        $users = User::get();
        $cobertura_id = $request->has('cobertura_id') ? $request->cobertura_id : session('cobertura_id', null);
        $centro_id = $request->has('centro_id') ? $request->centro_id : session('centro_id', null);
        $profesional_id = $request->has('profesional_id') ? $request->profesional_id : session('profesional_id', null);
        $user_id = $request->has('user_id') ? $request->user_id : session('user_id', null);
        $nombre = $request->has('nombre') ? $request->nombre : session('nombre', null);
        $nro_parte = $request->has('nro_parte') ? $request->nro_parte : session('nro_parte', null);
        $fec_desde = $request->has('submitInputs') ? $request->fec_desde : session('fec_desde', null);
        $fec_hasta = $request->has('submitInputs') ? $request->fec_hasta : session('fec_hasta', null);
        $estado_id = $request->has('estado_id') ? $request->estado_id : session('estado_id', null);
        $fec_desde_adm =  $request->has('submitInputs') ? $request->fec_desde_adm : session('fec_desde_adm', null);
        $fec_hasta_adm =  $request->has('submitInputs') ? $request->fec_hasta_adm : session('fec_hasta_adm', null);

// dd( $fec_desde_adm,  $fec_hasta_adm);
        $query = Parte_cab::vParteCab();
        if (!empty($cobertura_id)) {
            $query->where('cobertura_id', '=', $cobertura_id);
        }
        if (!empty($centro_id)) {
            $query->where('centro_id', '=', $centro_id);
        }
        if (!empty($profesional_id)) {
            $query->where('profesional_id', '=', $profesional_id);
        }
        if (!empty($estado_id)) {
            $query->where('estado_id', '=', $estado_id);
        }
        if (!empty($user_id)) {
            $query->where('user_id', '=', $user_id);
        }
        if (!empty($nombre)) {
            $query->where('paciente', 'like', "%".$nombre."%");
        }
        if (!empty($fec_desde)) {
            $query->where('fec_prestacion_orig', '>=', $fec_desde);
        }
        if (!empty($fec_hasta)) {
            $query->where('fec_prestacion_orig', '<=', $fec_hasta);
        }
        if (!empty($fec_desde_adm)) {
            $query->where('created_at', '>=', $fec_desde_adm);
        }
        if (!empty($fec_hasta_adm)) {
            $query->where('created_at', '<=', Carbon::parse($fec_hasta_adm)->addDay()->format('Y-m-d'));
        }
        if (!empty($nro_parte)) {
            $query->where('id', '=', $nro_parte);
        }
        $partes = $query->orderBy('created_at', 'asc')
                    ->paginate();

        // guardo el filtro en session
        session()->put('cobertura_id', $cobertura_id);
        session()->put('centro_id', $centro_id);
        session()->put('profesional_id', $profesional_id);
        session()->put('nombre', $nombre);
        session()->put('estado_id', $estado_id);
        session()->put('user_id', $user_id);
        session()->put('nro_parte', $nro_parte);
        session()->put('fec_desde', $fec_desde);
        session()->put('fec_hasta', $fec_hasta);
        session()->put('fec_desde_adm', $fec_desde_adm);
        session()->put('fec_hasta_adm', $fec_hasta_adm);
        session()->put('nro_parte', $nro_parte);

        // Ver la consulta SQL y los bindings
// $sql = $query->toSql();
// $bindings = $query->getBindings();
// dd($request, $sql, $bindings);

        return view("cargas.cab.parte", compact(
            "partes",
            "coberturas",
            "centros",
            "users",
            "estados",
            "profesionales"
        ));
    }

    public function create()
    {
        $centro_id = User::find(Auth()->user()->id)->centro_id;

        if ($centro_id) {
            $centros = Centro::where("id", $centro_id)->get();
        } else {
            $centros = Centro::orderby("nombre")->get();
        }
        // dd($centros);
        $paciente = new Paciente();
        $coberturas = Cobertura::orderby("nombre")->get();
        $profesionales = Profesional::get();
        $parte = new Parte_cab();
        $parte_id = null;

        return view('cargas.cab.create', compact('parte', 'centros', 'paciente', 'coberturas', 'profesionales', 'parte_id'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'required|string|max:20',
            'cobertura_id' => 'required',
            'centro_id' => 'required',
            'profesional_id' => 'required',
            'nombre' => 'required',
            'fec_nacimiento' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $year = explode('-', $value)[0];
                    $currentYear = date('Y');
                    if ($year < 1900 || $year > $currentYear) {
                        $fail("El campo fecha de nacimiento debe ser un año entre 1900 y $currentYear.");
                    }
                },
            ],
            'fec_prestacion' => ['required','date',
                function ($attribute, $value, $fail) {
                    $year = explode('-', $value)[0];
                    $currentYear = date('Y');
                    if ($year < 1900 || $year > $currentYear) {
                        $fail("El campo fecha de prestación debe ser un año entre 1900 y $currentYear.");
                    }
                }
            ]
        ]);

        $paciente = Paciente::where('dni', $request->dni)->first();
        if (empty($paciente)) {
            $paciente = new Paciente();
        }
        $paciente->dni = $request->dni;
        $paciente->nombre = $request->nombre;
        $paciente->fec_nacimiento = $request->fec_nacimiento;
        $paciente->save();
        $paciente_id = $paciente->id;

        if (empty($request->parte_id)) {
            $parte = new Parte_cab();
            $msg = "creado";
        } else {
            $parte = Parte_cab::where('id', $request->parte_id)->first();
            $msg = "actualizado";
        }
        $parte->paciente_id = $paciente_id;
        $parte->centro_id = $request->centro_id;
        $parte->cobertura_id = $request->cobertura_id;
        $parte->profesional_id = $request->profesional_id;
        $parte->observaciones = $request->observaciones;
        $parte->fec_prestacion = $request->fec_prestacion;
        $parte->user_id = Auth()->user()->id;
        $parte->save();

        return back()->withInput()
            ->with(['success' => "Se ha $msg la cabecera del parte correctamente, nro: {$parte->id}.",
                    'parte_id' => $parte->id]);
    }

    public function edit($id)
    {
        $parte = Parte_cab::find($id);
        $paciente = Paciente::where('id', $parte->paciente_id)->first();
        $centros = Centro::orderby("nombre")->get();
        $coberturas = Cobertura::orderby("nombre")->get();
        $profesionales = Profesional::get();
        $parte_id = $parte->id;

        return view('cargas.cab.create', compact('parte', 'centros', 'paciente', 'coberturas', 'profesionales', 'parte_id'));
    }

    public function destroy($id)
    {
        $parte = Parte_cab::find($id);
        //si esta ingresado o observado no se puede borrar
        if (in_array($parte->estado, [1,2])) {
            return redirect()->back()->with('error', "No es posible borrar el parte {$parte->id} con el estado actual.");
        }
        $parte->delete();

        return redirect()->route('partes_cab.filtrar')
        ->with('success', 'Parte borrado correctamente.');
    }

    public function createDet(int $id)
    {
        $documentos = Documento::where("tipo", "like", "%parte%")->get();
        $estados = estado::get();
        $partes = Parte_det::with('documento')->where('parte_cab_id', $id)->paginate(5);
        $parte = new Parte_det();
        $observaciones = Parte_cab::find($id)->observaciones;
        $parte_cab_id = $id;

        return view('cargas.det.form', compact('estados', 'parte', 'partes', 'documentos', 'parte_cab_id', 'observaciones'));
    }

    public function storeDet(Request $request)
    {
        $validatedData = $request->validate([
            'parte_cab_id' => 'required|exists:partes_cab,id',
            'documento_id' => 'required|exists:documentos,id',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5548',
            'nro_hoja' => 'required|integer'
        ]);

        $archivo = $request->file('archivo');
        $archivoNombre = $archivo->getClientOriginalName();

        // Guardar en el disco 'partes' configurado en filesystems.php
        $path = Storage::disk('partes')->putFileAs($request->parte_cab_id, $archivo, $archivoNombre);

        // Guardar el path en la base de datos
        $parteDet = new Parte_det();
        $parteDet->parte_cab_id = $request->parte_cab_id;
        $parteDet->documento_id = $request->documento_id;
        $parteDet->nro_hoja = $request->nro_hoja;
        $parteDet->path = $archivoNombre;
        $parteDet->save();

        return back();
    }

    public function download($id)
    {
        $parte = Parte_det::find($id);
        $rutaArchivo = storage_path('app/public/partes/') . $parte->parte_cab_id . "/" . $parte->path;
        if (!Storage::disk('partes')->exists($parte->parte_cab_id . "/" . $parte->path)) {
            abort(404, 'El archivo no existe.');
        }

        // Obtener el nombre original del archivo
        $nombreOriginal = basename($rutaArchivo);

        // Retornar una respuesta de descarga
        // return response()->download($rutaArchivo, $nombreOriginal);
        return response()->file($rutaArchivo);
    }

    public function destroyDet(int $id)
    {
        $parte_det = Parte_det::find($id);
        $parte = Parte_cab::where("id", $parte_det->parte_cab_id)->first();
        if (!in_array($parte->estado_id, [1,2])) {
            return redirect()->route('partes_det.create', $parte->id)
            ->with('error', 'No es posible borrar la documentación con el estado actual del parte.');
        }
        $parte_det->delete();

        return redirect()->route('partes_det.create', $parte->id)
        ->with('success', 'Detalle de Parte borrado correctamente.');
    }

    // public function getData(Request $request)
    // {
    //     $query = Parte_cab::v_parte_cab()->select([
    //         'id', 'centro', 'profesional', 'paciente', 'fec_prestacion', 'sigla',
    //         'est_id', 'est_descripcion', 'observacion', 'cantidad', 'name', 'created_at'
    //     ]);
    
    //     return DataTables::of($query)
    //         ->addColumn('acciones', function ($item) {
    //             return view('cargas.cab.partials.partes_acciones', ['item' => $item])->render();
    //         })
    //         ->editColumn('est_descripcion', function ($item) {
    //             $badgeColor = match ($item->est_id) {
    //                 1 => 'primary',
    //                 2 => 'danger',
    //                 3 => 'warning',
    //                 4 => 'info',
    //                 5 => 'secondary',
    //                 default => 'success'
    //             };
    //             return view('cargas.cab.partials.estados', ['item' => $item, 'badgeColor' => $badgeColor])->render();
    //         })
    //         ->addColumn('id_tooltip', function ($item) {
    //             return view('cargas.cab.partials.id_tooltip', ['item' => $item])->render();
    //         })
    //         ->rawColumns(['acciones', 'est_descripcion', 'id'])
    //         ->make(true);
    // }
}
