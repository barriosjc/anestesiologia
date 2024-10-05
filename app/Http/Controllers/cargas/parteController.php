<?php

namespace App\Http\Controllers\cargas;

use App\Models\User;
use App\Models\Centro;
use App\Models\Paciente;
use App\Models\Cobertura;
use App\Models\Documento;
use App\Models\Parte_cab;
use App\Models\Parte_det;
use App\Models\Profesional;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ParteController extends Controller
{
    public function index()
    {
        $partes = parte_cab::v_parte_cab()
            ->where("centro_id", Auth()->user()->centro_id)
            ->orderBy("id", "desc")
            ->get();

        return view('cargas.cab.parte', compact('partes'));
            // ->with('i', (request()->input('page', 1) - 1) * $partes->perPage());
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
        // dd($parte);
        // return redirect()->route('partes_cab.index')
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

        return redirect()->route('partes_cab.index')
        ->with('success', 'Parte borrado correctamente.');
    }

    public function createDet(int $id)
    {
        $documentos = Documento::where("tipo", "like", "%parte%")->get();
        $partes = Parte_det::with('documento')->where('parte_cab_id', $id)->paginate(5);
        $parte = new Parte_det();
        $parte_cab_id = $id;

        return view('cargas.det.form', compact('parte', 'partes', 'documentos', 'parte_cab_id'));
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
}
