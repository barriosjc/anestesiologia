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
        $Centros = Centro::paginate(10);

        return view('entidades.Centro.index', compact('Centros'))
            ->with('i', (request()->input('page', 1) - 1) * $Centros->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $Centros = new Centro;
        return view('entidades.Centro.create', compact('Centros'));
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

        $Centro = new Centro;
        $Centro->nombre = $request['nombre'];
        $Centro->cuit = $request['cuit'];
        $Centro->telefono = $request['telefono'];
        $Centro->contacto = $request['contacto'];
        $Centro->save();
        
        return redirect()->route('Centros.index')
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
        $Centro = Centro::find($id);

        return view('entidades.Centro.show', compact('Centro'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $Centros = Centro::find($id);

        return view('entidades.Centro.edit', compact('Centros'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Centro $Centro
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Centro $Centro)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'cuit' => 'required|string|max:15',
            'telefono' => 'nullable|string|max:45',
            'contacto' => 'nullable|string|max:45'
        ]);

        $Centro->nombre = $request['nombre'];
        $Centro->cuit = $request['cuit'];
        $Centro->telefono = $request['telefono'];
        $Centro->contacto = $request['contacto'];
        $Centro->save();


        return redirect()->route('Centros.index')
            ->with('success', 'Centro actualizado correctamente.');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $Centro = Centro::find($id)->delete();

        return redirect()->route('Centros.index')
            ->with('success', 'Centro borrado correctamente.');
    }

}
