<?php

namespace App\Http\Controllers\cargas;

use App\Models\User;
use App\Models\Centro;
use App\Models\paciente;
use App\Models\cobertura;
use App\Models\Documento;
use App\Models\Parte_cab;
use App\Models\Parte_det;
use App\Models\Profesional;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class parteController extends Controller
{
    public function index()
    {
        // $partes = parte_cab::v_parte_cab()->paginate(5);
        $partes = parte_cab::v_parte_cab()
            ->orderBy("id","desc")
            ->get();

        return view('cargas.cab.parte', compact('partes'));
            // ->with('i', (request()->input('page', 1) - 1) * $partes->perPage());
    }

    public function create() 
    {
        $centro_id = User::find(Auth()->user()->id)->centro_id;
        if($centro_id) {
            $centros = centro::where("id", $centro_id)->get();
        }else {
            $centros = centro::get();
        }
        // dd($centros);
        $paciente = new paciente;
        $coberturas = cobertura::get();
        $profesionales = Profesional::get();
        $parte = new Parte_cab;
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
            'fec_nacimiento' => 'required|date|before_or_equal:today',
            'fec_prestacion' => 'required|date|before_or_equal:today'
        ]);

        $paciente = paciente::where('dni', $request->dni)->first();
        if (empty($paciente)  ) {
            $paciente = new paciente;
        }
        $paciente->dni = $request->dni;
        $paciente->nombre = $request->nombre;
        $paciente->fec_nacimiento = $request->fec_nacimiento;
        $paciente->save();
        $paciente_id = $paciente->id;

        if (empty($request->parte_id)) {
            $parte = new Parte_cab;
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
            ->with(['success' => "Se ha $msg la cabecera del parte correctamente."]);
    }

    public function edit($id)
    {
        $parte = Parte_cab::find($id);
        $paciente = paciente::where('id', $parte->paciente_id)->first();
        $centros = centro::get();
        $coberturas = cobertura::get();
        $profesionales = Profesional::get();
        $parte_id = $parte->id;

        return view('cargas.cab.create', compact('parte', 'centros', 'paciente', 'coberturas', 'profesionales', 'parte_id'));
    }

    public function destroy($id)
    {
        $parte = Parte_cab::find($id);
        $parte->delete();

        return redirect()->route('partes_cab.index')
        ->with('success', 'Parte borrado correctamente.');
    }

    public function create_det(int $id) 
    {
        $documentos = Documento::get();
        $partes = Parte_det::with('documento')->where('parte_cab_id' , $id)->paginate(5);
        $parte = new Parte_det;
        $parte_cab_id = $id;

        return view('cargas.det.form', compact('parte', 'partes', 'documentos', 'parte_cab_id'));
    } 

    public function store_det(Request $request) 
    {
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
        $rutaArchivo = storage_path('app/public/partes/') . $parte->parte_cab_id ."/". $parte->path;
        if (!Storage::disk('partes')->exists($parte->parte_cab_id ."/". $parte->path)) {
            abort(404, 'El archivo no existe.');
        }

        // Obtener el nombre original del archivo
        $nombreOriginal = basename($rutaArchivo);

        // Retornar una respuesta de descarga
        return response()->download($rutaArchivo, $nombreOriginal);
    }
    
    public function det_destroy($id)
    {
        $parte = Parte_det::find($id);
        $parte->delete();

        return redirect()->route('partes_det.create')
        ->with('success', 'Detalle de Parte borrado correctamente.');
    }    
}
