<?php

namespace App\Http\Controllers\entidades;

use App\Models\Cobertura;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class CoberturaController
 * @package App\Http\Controllers
 */
class CoberturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coberturas = Cobertura::get();
        // ::paginate();

        return view('entidades.cobertura.index', compact('coberturas'));
            // ->with('i', (request()->input('page', 1) - 1) * $coberturas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $coberturas = new Cobertura;
        return view('entidades.cobertura.create', compact('coberturas'));
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
            'sigla' => 'required|string|max:45',
            'grupo' => 'required|int',
            'edad_desde' => 'nullable|int',
            'edad_hasta' => 'nullable|int',
            'porcentaje_adic' => 'nullable|int'
        ]);

        $cobertura = new Cobertura;
        $cobertura->nombre = $request['nombre'];
        $cobertura->cuit = $request['cuit'];
        $cobertura->sigla = $request['sigla'];
        $cobertura->grupo = $request['grupo'];
        $cobertura->edad_desde = $request['edad_desde'];
        $cobertura->edad_hasta = $request['edad_hasta'];
        $cobertura->porcentaje_adic = $request['porcentaje_adic'];
        $cobertura->save();
        
        return redirect()->route('coberturas.index')
            ->with('success', 'Cobertura creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cobertura = Cobertura::find($id);

        return view('entidades.cobertura.show', compact('Cobertura'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coberturas = Cobertura::find($id);

        return view('entidades.cobertura.edit', compact('coberturas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Cobertura $cobertura
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cobertura $cobertura)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'cuit' => 'required|string|max:15',
            'sigla' => 'required|string|max:45',
            'grupo' => 'required|int',
            'edad_desde' => 'nullable|int',
            'edad_hasta' => 'nullable|int',
            'porcentaje_adic' => 'nullable|int'
        ]);

        $cobertura->nombre = $request['nombre'];
        $cobertura->cuit = $request['cuit'];
        $cobertura->sigla = $request['sigla'];
        $cobertura->grupo = $request['grupo'];
        $cobertura->edad_desde = $request['edad_desde'];
        $cobertura->edad_hasta = $request['edad_hasta'];
        $cobertura->porcentaje_adic = $request['porcentaje_adic'];
        $cobertura->save();


        return redirect()->route('coberturas.index')
            ->with('success', 'Cobertura actualizado correctamente.');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $cobertura = Cobertura::find($id)->delete();

        return redirect()->route('coberturas.index')
            ->with('success', 'Cobertura borrado correctamente.');
    }

}
