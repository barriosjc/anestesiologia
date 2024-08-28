<?php

namespace App\Http\Controllers\entidades;

use App\Models\Centro;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class CentroController
 * @package App\Http\Controllers
 */
class CentroController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $centros = Centro::paginate(10);

        return view('entidades.centro.index', compact('centros'))
            ->with('i', (request()->input('page', 1) - 1) * $centros->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $centros = new Centro;
        return view('entidades.centro.create', compact('centros'));
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
            'cuit' => 'required|string|max:15',
            'telefono' => 'nullable|string|max:45',
            'contacto' => 'nullable|string|max:45'
        ]);

        $centro = new Centro;
        $centro->nombre = $request['nombre'];
        $centro->cuit = $request['cuit'];
        $centro->telefono = $request['telefono'];
        $centro->contacto = $request['contacto'];
        $centro->save();
        
        return redirect()->route('centros.index')
            ->with('success', 'Centro creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $centro = Centro::find($id);

        return view('entidades.centro.show', compact('centro'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $centros = Centro::find($id);

        return view('entidades.centro.edit', compact('centros'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  centro $centro
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Centro $centro)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'cuit' => 'required|string|max:15',
            'telefono' => 'nullable|string|max:45',
            'contacto' => 'nullable|string|max:45'
        ]);

        $centro->nombre = $request['nombre'];
        $centro->cuit = $request['cuit'];
        $centro->telefono = $request['telefono'];
        $centro->contacto = $request['contacto'];
        $centro->save();


        return redirect()->route('centros.index')
            ->with('success', 'Centro actualizado correctamente.');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $centro = Centro::find($id)->delete();

        return redirect()->route('centros.index')
            ->with('success', 'Centro borrado correctamente.');
    }

}
