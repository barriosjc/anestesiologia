<?php

namespace App\Http\Controllers\entidades;

use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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
            'matricula' => 'required|string|max:45'
        ]);

        $profesional = new profesional;
        $profesional->nombre = $request['nombre'];
        $profesional->email = $request['email'];
        $profesional->telefono = $request['telefono'];
        $profesional->matricula = $request['matricula'];
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
    public function update(Request $request, profesional $profesional)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'email' => 'required|email',
            'telefono' => 'required|string|max:45',
            'matricula' => 'required|string|max:45'
        ]);

        $profesional->nombre = $request['nombre'];
        $profesional->email = $request['email'];
        $profesional->telefono = $request['telefono'];
        $profesional->matricula = $request['matricula'];
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

}
