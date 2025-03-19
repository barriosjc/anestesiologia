<?php

namespace App\Http\Controllers\entidades;

use Illuminate\Http\Request;
use App\Models\NomPracticasEstudio;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNomencladorRequest;

class NomPracticasEstudioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(int $id, $tipo)
    {
        $nom_padre_id = $id;
        $nom_practicas_estudios = NomPracticasEstudio::with('nomPadre')->where('nom_padre_id', $id)->paginate(10);

        return view('entidades.nom_practicas_estudios.index', compact('nom_practicas_estudios', 'nom_padre_id', 'tipo'));
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($nom_padre_id)
    {
        $nom_padre_id = $nom_padre_id;  
        $nom_practicas_estudios = new NomPracticasEstudio();

        return view('entidades.nom_practicas_estudios.create', compact('nom_padre_id', 'nom_practicas_estudios'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreNomencladorRequest $request)
    {
        $validatedData = $request->validated();
        $nomenclador = NomPracticasEstudio::updateOrCreate(['id' => $request['id']], $request->all());
        $nom_padre_id = $nomenclador->nom_padre_id;

        return redirect()->route('nom_practicas_estudios.index', compact('nom_padre_id'))
            ->with('success', 'Práctica o estudio creada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $nom_practicas_estudios = NomPracticasEstudio::find($id);
        $nom_padre_id = $nom_practicas_estudios->nom_padre_id;

        return view('entidades.nom_practicas_estudios.create', compact('nom_padre_id', 'nom_practicas_estudios'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
