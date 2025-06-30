<?php

namespace App\Http\Controllers\entidades;

use App\Models\Gerenciadora;
use App\Models\Cobertura;
use App\Models\GerenciadoraCoberturaNomPadre;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class CoberturaController
 * @package App\Http\Controllers
 */
class GerenciadoraCoberturaPadreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gerenciadora_cobertura_padre = GerenciadoraCoberturaNomPadre::with(['gerenciadora', 'cobertura', 'nomPadre'])->paginate(20);


        return view('entidades.gerenciadora_cobertura_padre.index', compact('gerenciadora_cobertura_padre'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $coberturas = new Cobertura;
        return view('entidades.gerenciadora_cobertura_padre.create', compact('coberturas'));
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
            'gerenciadora_id' => 'required|int',
            'cobertura_id' => 'required|int',
            'nom_nombre_id' => 'required|int',
        ]);

        $erenciadoraCoberturaNomPadre = new GerenciadoraCoberturaNomPadre;
        $erenciadoraCoberturaNomPadre->save($validated);
        
        // return redirect()->route('GerenciadoraCoberturaNomPadre.index')
        //     ->with('success', 'Cobertura creado correctamente.');

        return back()->with('success', 'Cobertura creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $gerenciadora_cobertura_padre = GerenciadoraCoberturaNomPadre::find($id);

        return view('entidades.gerenciadora_cobertura_padre.edit', compact('gerenciadora_cobertura_padre'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Cobertura $cobertura
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'gerenciadora_id' => 'required|int',
            'cobertura_id' => 'required|int',
            'nom_nombre_id' => 'required|int',
        ]);
        
        $gerenciadoraCoberturaNomPadre = GerenciadoraCoberturaNomPadre::find($id);
        $gerenciadoraCoberturaNomPadre->save($validated);


        return redirect()->route('gerenciadoraCoberturaNomPadre.index')
            ->with('success', 'GerenciadoraCoberturaNomPadre actualizado correctamente.');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        GerenciadoraCoberturaNomPadre::find($id)->delete();

        return redirect()->route('GerenciadoraCoberturaNomPadre.index')
            ->with('success', 'GerenciadoraCoberturaNomPadre borrado correctamente.');
    }

    public function buscar(Request $request)
    {
        $text = $request->text;
        $gerenciadora_cobertura_padre = GerenciadoraCoberturaNomPadre::where('nombre', 'like', "%{$text}%")
            ->whereOr('nombre', 'like', "%{$text}%")
            ->get();

        return view('entidades.gerenciadora_cobertura_padre.index', compact('gerenciadora_cobertura_padre'));
    }
}
