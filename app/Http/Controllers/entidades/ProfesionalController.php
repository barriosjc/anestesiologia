<?php

namespace App\Http\Controllers\entidades;

use App\Models\Documento;
use App\Models\Profesional_documento;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfesionalController extends Controller
{
    public function cargarDocum(int $id)
    {
        $documentos = Documento::where("tipo", "like", "%prof%")->get();
        $prof_docum = Profesional_documento::with('documento')->where('profesional_id', $id)->paginate(5);
        $profesional_id = $id;

        return view('entidades.profesional.documentos', compact('prof_docum', 'documentos', 'profesional_id'));
    }

    public function guardarDocum(Request $request)
    {
        $validatedData = $request->validate([
            'profesional_id' => 'required|exists:profesionales,id',
            'documento_id' => 'required|exists:documentos,id',
            'nro_hoja' => 'required|integer',
            'fecha_vcto' => [
                'nullable',
                'date',
                Rule::requiredIf(function () use ($request) {
                    $documento = Documento::find($request->documento_id);
                    return $documento && strtoupper($documento->vencimiento) === 'S';
                })
            ]
        ], [
            'fecha_vcto.required' => 'La fecha de vencimiento es obligatoria para el tipo de documento ingresado.'
        ]);

        $archivo = $request->file('archivo');
        $archivoNombre = $archivo->getClientOriginalName();

        Storage::disk('usuarios')->putFileAs($request->profesional_id, $archivo, $archivoNombre);

        $prof_docum = new Profesional_documento();
        $prof_docum->profesional_id = $request->profesional_id;
        $prof_docum->documento_id = $request->documento_id;
        $prof_docum->nro_hoja = $request->nro_hoja;
        $prof_docum->path = $archivoNombre;
        $prof_docum->fecha_vcto = $request->fecha_vcto;
        $prof_docum->save();
        return back();
    }

    public function downloadDocum($id)
    {
        $prof_docum = Profesional_documento::find($id);
        $rutaArchivo = storage_path('app/public/usuarios/') . $prof_docum->profesional_id ."/". $prof_docum->path;
        if (!Storage::disk('usuarios')->exists($prof_docum->profesional_id ."/". $prof_docum->path)) {
            abort(404, 'El archivo no existe.');
        }

        $nombreOriginal = basename($rutaArchivo);

        return response()->download($rutaArchivo, $nombreOriginal);
    }

    public function borrarDocum(int $id)
    {
        $prof_docum = Profesional_documento::find($id);
        $prof_docum->delete();

        return back();
    }
}
