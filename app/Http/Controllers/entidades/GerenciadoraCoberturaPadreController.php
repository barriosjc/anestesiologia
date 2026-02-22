<?php

namespace App\Http\Controllers\entidades;

use App\Models\Gerenciadora;
use App\Models\Cobertura;
use App\Models\GerenciadoraCoberturaNomPadre;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

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
        $coberturas = Cobertura::get();
        $gerenciadoras = Gerenciadora::get();
        $gerenciadora_cobertura_padre = new GerenciadoraCoberturaNomPadre();

        return view('entidades.gerenciadora_cobertura_padre.create', compact('coberturas', 'gerenciadoras', 'gerenciadora_cobertura_padre'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'gerenciadora_id' => 'required|integer',
                'cobertura_id'    => 'required|integer',
                'nom_padre_id'    => [
                    'required',
                    'integer',
                    Rule::unique('gerenciadoras_coberturas_nom_padres')
                        ->where(
                            fn($query) => $query
                                ->where('gerenciadora_id', $request->gerenciadora_id)
                                ->where('cobertura_id', $request->cobertura_id)
                        ),
                ],
            ],
            [
                'nom_padre_id.unique' =>
                'Ya existe una relación con la misma Gerenciadora, Cobertura y Nomenclador Padre que se cargó anteriormente.',
            ]
        );

        $gerenciadoraCoberturaNomPadre = new GerenciadoraCoberturaNomPadre;
        $gerenciadoraCoberturaNomPadre->gerenciadora_id = $validated['gerenciadora_id'];
        $gerenciadoraCoberturaNomPadre->cobertura_id = $validated['cobertura_id'];
        $gerenciadoraCoberturaNomPadre->nom_padre_id = $validated['nom_padre_id'];
        $gerenciadoraCoberturaNomPadre->save();

        return back()->with('success', 'Cobertura creado correctamente.');
    }


    public function update(Request $request, int $id)
    {
        $validated = $request->validate(
            [
                'gerenciadora_id' => 'required|integer',
                'cobertura_id'    => 'required|integer',
                'nom_padre_id'    => [
                    'required',
                    'integer',
                    Rule::unique('gerenciadoras_coberturas_nom_padres')
                        ->where(
                            fn($query) => $query
                                ->where('gerenciadora_id', $request->gerenciadora_id)
                                ->where('cobertura_id', $request->cobertura_id)
                        )
                        ->ignore($id),
                ],
            ],
            [
                'nom_padre_id.unique' =>
                'Ya existe una relación con la misma Gerenciadora, Cobertura y Nomenclador Padre que se cargó anteriormente.',
            ]
        );

        $gerenciadoraCoberturaNomPadre = GerenciadoraCoberturaNomPadre::findOrFail($id);
        $gerenciadoraCoberturaNomPadre->gerenciadora_id = $validated['gerenciadora_id'];
        $gerenciadoraCoberturaNomPadre->cobertura_id    = $validated['cobertura_id'];
        $gerenciadoraCoberturaNomPadre->nom_padre_id    = $validated['nom_padre_id'];
        $gerenciadoraCoberturaNomPadre->save();

        return redirect()->route('gerenciadora_cobertura_padre.index')
            ->with('success', 'GerenciadoraCoberturaNomPadre actualizado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coberturas = Cobertura::get();
        $gerenciadoras = Gerenciadora::get();
        $gerenciadora_cobertura_padre = GerenciadoraCoberturaNomPadre::where('id', $id)->first();

        return view('entidades.gerenciadora_cobertura_padre.edit', compact('coberturas', 'gerenciadoras', 'gerenciadora_cobertura_padre'));
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        GerenciadoraCoberturaNomPadre::where('id', $id)->delete();

        return redirect()->route('gerenciadora_cobertura_padre.index')
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
