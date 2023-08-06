<?php

namespace App\Http\Controllers;

use App\Models\Grupal;
use Illuminate\Http\Request;

/**
 * Class GrupalController
 * @package App\Http\Controllers
 */
class GrupalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $grupals = Grupal::simplepaginate();

        return view('grupal.index', compact('grupals'))
            ->with('i', (request()->input('page', 1) - 1) * $grupals->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $grupal = new Grupal();
        return view('grupal.create', compact('grupal'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Grupal::$rules);

        $grupal = Grupal::create($request->all());

        return redirect()->route('grupals.index')
            ->with('success', 'Grupal created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $grupal = Grupal::find($id);

        return view('grupal.show', compact('grupal'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $grupal = Grupal::find($id);

        return view('grupal.edit', compact('grupal'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Grupal $grupal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Grupal $grupal)
    {
        request()->validate(Grupal::$rules);

        $grupal->update($request->all());

        return redirect()->route('grupals.index')
            ->with('success', 'Grupal actualizada correctamente');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $grupal = Grupal::find($id)->delete();

        return redirect()->route('grupals.index')
            ->with('success', 'Grupal deleted successfully');
    }
}
