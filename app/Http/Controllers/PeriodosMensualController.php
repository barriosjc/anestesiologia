<?php

namespace App\Http\Controllers;

use App\Models\PeriodosMensual;
use Illuminate\Http\Request;

/**
 * Class PeriodosMensualController
 * @package App\Http\Controllers
 */
class PeriodosMensualController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $periodosMensuals = PeriodosMensual::paginate();

        return view('periodos-mensual.index', compact('periodosMensuals'))
            ->with('i', (request()->input('page', 1) - 1) * $periodosMensuals->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $periodosMensual = new PeriodosMensual();
        return view('periodosmensual.create', compact('periodosMensual'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(PeriodosMensual::$rules);

        $periodosMensual = PeriodosMensual::create($request->all());

        return redirect()->route('periodos-mensual.index')
            ->with('success', 'PeriodosMensual created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $periodosMensual = PeriodosMensual::find($id);

        return view('periodosmensual.show', compact('periodosMensual'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $periodosMensual = PeriodosMensual::find($id);

        return view('periodosmensual.edit', compact('periodosMensual'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  PeriodosMensual $periodosMensual
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PeriodosMensual $periodosMensual)
    {
        request()->validate(PeriodosMensual::$rules);

        $periodosMensual->update($request->all());

        return redirect()->route('periodos-mensual.index')
            ->with('success', 'PeriodosMensual updated successfully');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $periodosMensual = PeriodosMensual::find($id)->delete();

        return redirect()->route('periodos-mensual.index')
            ->with('success', 'PeriodosMensual deleted successfully');
    }
}
