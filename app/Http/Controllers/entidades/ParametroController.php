<?php

namespace App\Http\Controllers\entidades;

use App\Models\Parametro;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class ParametroController
 * @package App\Http\Controllers
 */
class ParametroController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parametros = Parametro::paginate(10);

        return view('entidades.parametro.index', compact('parametros'))
            ->with('i', (request()->input('page', 1) - 1) * $parametros->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parametros = new Parametro;
        return view('entidades.parametro.create', compact('parametros'));
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
            'nombre' => 'required|string|max:50',
            'valor' => 'required|string|max:50'
        ]);

        $parametro = new Parametro;
        $parametro->nombre = $request['nombre'];
        $parametro->valor = $request['valor'];
        $parametro->save();
        
        return redirect()->route('parametros.index')
            ->with('success', 'Parametro creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $parametros = Parametro::find($id);

        return view('entidades.parametro.edit', compact('parametros'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Parametro $parametro
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Parametro $parametro)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'valor' => 'required|string|max:50'
        ]);

        $parametro->nombre = $request['nombre'];
        $parametro->valor = $request['valor'];
        $parametro->save();


        return redirect()->route('parametros.index')
            ->with('success', 'Parametro actualizado correctamente.');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $parametro = Parametro::find($id)->delete();

        return redirect()->route('parametros.index')
            ->with('success', 'Parametro borrado correctamente.');
    }
}
