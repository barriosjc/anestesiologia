<?php

namespace App\Http\Controllers;

use App\Models\Opcion;
use Illuminate\Http\Request;

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
        $opciones = Opcion::paginate();

        return view('opcion.index', compact('opciones'))
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
        return view('opcion.create', compact('opciones'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Opcion::$rules);

        $opciones = Opcion::create($request->all());

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

        return view('opcion.show', compact('opciones'));
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

        return view('opcion.edit', compact('opciones'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Opcion $opciones
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Opcion $opciones)
    {
        request()->validate(Opcion::$rules);

        $opciones->update($request->all());

        return redirect()->route('opcion.index')
            ->with('success', 'Opcion updated successfully');
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
