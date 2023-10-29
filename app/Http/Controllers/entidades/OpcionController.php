<?php

namespace App\Http\Controllers\entidades;

use App\Models\Opcion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

/**
 * Class OpcionController
 * @package App\Http\Controllers
 */
class OpcionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $opciones = Opcion::where("empresas_id", session('empresa')->id)->simplepaginate(5);

        return view('entidades.opcion.index', compact('opciones'))
            ->with('i', (request()->input('page', 1) - 1) * $opciones->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $opciones = new Opcion();

        return view('entidades.opcion.create', compact('opciones'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        //dd($request);
        $validate = $request->validate( [
            'descripcion' => 'required',
            'detalle' => 'required',
            'imagen' => 'required',             
            'style' => 'required',
        'habilitada' => 'required',
        'puntos' => 'required',
        ]);

        $opciones = new Opcion;
        foreach ($validate as $key => $value) {
            $opciones->$key = $value;
        }

        $opciones->habilitada = $opciones->habilitada ? 1 : 0;
        $opciones->empresas_id = session('empresa')->id;
        $empresa = session('empresa')->uri;
         //dd( Storage::disk('empresas'));
        $originalName = $request->file('imagen')->getClientOriginalName();
        $path = "$empresa/opciones/".$originalName;
        Storage::disk('empresas')->put($path, $request->file('imagen')->get());

        $opciones->imagen = $path;

        $opciones->save();
        
        return redirect()->route('opcion.index')
            ->with('success', 'Opcion created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $opciones = Opcion::find($id);

        return view('entidades.opcion.show', compact('opciones'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $opciones = Opcion::find($id);

        return view('entidades.opcion.edit', compact('opciones'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Opcion $opciones
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $opcion)
    {      
        $opciones = opcion::where("id", $opcion)->first();  
        $validate = $request->validate( [
            'descripcion' => 'required',
            'detalle' => 'required',
            'imagen' => Rule::requiredIf(empty($opciones->imagen)),             
            'style' => 'required',
            'habilitada' => 'required',
            'puntos' => 'required',
        ]);

        // $opciones = Opcion::where("id", $opcion->id)->first();
        //   dd($opciones);
        foreach ($validate as $key => $value) {
            $opciones->$key = $value;
        }
        $opciones->habilitada = $request->habilitada ? 1 : 0;
        $opciones->empresas_id = session('empresa')->id;
        $empresa = session('empresa')->uri;

        if($request->hasFile('imagen')){
            $originalName = $request->file('imagen')->getClientOriginalName();
            $path = "$empresa/opciones/".$originalName;
            Storage::disk('empresas')->put($path, $request->file('imagen')->get());
            $opciones->imagen = $path;
        }
        $opciones->save();

        return redirect()->route('opcion.index')
            ->with('success', 'Opcion actualizada correctamente');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $opciones = Opcion::find($id)->delete();

        return redirect()->route('opcion.index')
            ->with('success', 'Opcion deleted successfully');
    }
}
