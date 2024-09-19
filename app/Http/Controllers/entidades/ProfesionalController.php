<?php

namespace App\Http\Controllers\entidades;

use App\Models\Documento;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ProfesionalDocumento;
use Illuminate\Support\Facades\Storage;

/**
 * Class ProfesionalController
 * @package App\Http\Controllers
 */
class ProfesionalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $profesionales = profesional::paginate(10);

        return view('entidades.profesional.index', compact('profesionales'))
            ->with('i', (request()->input('page', 1) - 1) * $profesionales->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $profesionales = new profesional;
        return view('entidades.profesional.create', compact('profesionales'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'email' => 'required|email',
            'telefono' => 'required|string|max:45',
            'dni' => 'required|string|max:20'
        ]);

        $profesional = new profesional;
        $profesional->nombre = $request['nombre'];
        $profesional->email = $request['email'];
        $profesional->telefono = $request['telefono'];
        $profesional->dni = $request['dni'];
        $profesional->estado = isset($request['estado']) ? 1 : 0;;
        $profesional->save();
        
        return redirect()->route('profesionales.index')
            ->with('success', 'Médico creado correctamente.');
    }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  int $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show($id)
    // {
    //     $profesional = profesional::find($id);

    //     return view('entidades.profesional.show', compact('profesional'));
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $profesionales = profesional::find($id);

        return view('entidades.profesional.edit', compact('profesionales'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  profesional $profesional
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'email' => 'required|email',
            'telefono' => 'required|string|max:45',
            'dni' => 'required|string|max:20'
        ]);

        $profesional = profesional::find($id);
        $profesional->nombre = $request['nombre'];
        $profesional->email = $request['email'];
        $profesional->telefono = $request['telefono'];
        $profesional->dni = $request['dni'];
        $profesional->estado = isset($request['estado']) ? 1 : 0;;
        $profesional->save();


        return redirect()->route('profesionales.index')
            ->with('success', 'Médico actualizado correctamente.');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $profesional = profesional::find($id)->delete();

        return redirect()->route('profesionales.index')
            ->with('success', 'Médico borrado correctamente.');
    }

    // trabajar con detalle de documentacion
    public function cargar_docum(int $id) 
    {
        $documentos = Documento::where("tipo", "like", "%prof%")->get();
        $prof_docum = ProfesionalDocumento::with('documento')->where('profesional_id' , $id)->paginate(5);
        //$parte = new Parte_det;
        $profesional_id = $id;

        return view('entidades.profesional.documentos', compact( 'prof_docum', 'documentos', 'profesional_id'));
    } 

    public function guardar_docum(Request $request) 
    {
        // dd($request->file('archivo'));
        // 'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:10000',

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
        // dd("paso por aca 167");

        $archivo = $request->file('archivo');
        $archivoNombre = $archivo->getClientOriginalName();

        // Guardar en el disco 'partes' configurado en filesystems.php
        $path = Storage::disk('usuarios')->putFileAs($request->profesional_id, $archivo, $archivoNombre);

        // Guardar el path en la base de datos
        $prof_docum = new ProfesionalDocumento();
        $prof_docum->profesional_id = $request->profesional_id;
        $prof_docum->documento_id = $request->documento_id;
        $prof_docum->nro_hoja = $request->nro_hoja;
        $prof_docum->path = $archivoNombre; 
        $prof_docum->fecha_vcto = $request->fecha_vcto;
        $prof_docum->save();
        return back();
    }

    public function download_docum($id) 
    {
        $prof_docum = ProfesionalDocumento::find($id);
        $rutaArchivo = storage_path('app/public/usuarios/') . $prof_docum->profesional_id ."/". $prof_docum->path;
        if (!Storage::disk('usuarios')->exists($prof_docum->profesional_id ."/". $prof_docum->path)) {
            abort(404, 'El archivo no existe.');
        }

        // Obtener el nombre original del archivo
        $nombreOriginal = basename($rutaArchivo);

        return response()->download($rutaArchivo, $nombreOriginal);
    }
    
    public function borrar_docum(int $id)
    {
        $prof_docum = ProfesionalDocumento::find($id);
        // $profesional_id = $prof_docum->profesional_id;
        $prof_docum->delete();

        // return redirect()->route('partes_det.create', $prof_docum_id)
        // ->with('success', 'Detalle de Parte borrado correctamente.');
        return back();
    }
}
