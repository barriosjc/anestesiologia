<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use Illuminate\Http\Request;

/**
 * Class PeriodoController
 * @package App\Http\Controllers
 */
class PeriodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $periodos = Periodo::paginate();

        return view('periodo.index', compact('periodos'))
            ->with('i', (request()->input('page', 1) - 1) * $periodos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $periodo = new Periodo();
        return view('periodo.create', compact('periodo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Periodo::$rules);

        $periodo = Periodo::create($request->all());

        return redirect()->route('periodos.index')
            ->with('success', 'Periodo created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $periodo = Periodo::find($id);

        return view('periodo.show', compact('periodo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $periodo = Periodo::find($id);

        return view('periodo.edit', compact('periodo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Periodo $periodo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Periodo $periodo)
    {
        request()->validate(Periodo::$rules);

        $periodo->update($request->all());

        return redirect()->route('periodos.index')
            ->with('success', 'Periodo updated successfully');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $periodo = Periodo::find($id)->delete();

        return redirect()->route('periodos.index')
            ->with('success', 'Periodo deleted successfully');
    }
}
